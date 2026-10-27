"""
Nagad Direct Online Payment Gateway (API v3.3) - Python SDK
Author: Refat Rahman (https://refat.ovh | @refatbd)
License: MIT
"""

import base64
from datetime import datetime
import os
import re
from typing import Any, Dict, Optional, Union
from urllib.parse import parse_qs, urlparse

try:
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.asymmetric import padding
    from cryptography.hazmat.primitives.serialization import load_pem_private_key, load_pem_public_key
    HAS_CRYPTOGRAPHY = True
except ImportError:
    HAS_CRYPTOGRAPHY = False

import requests


class NagadException(Exception):
    """Base exception for Nagad Payment Gateway communication and crypto errors."""
    def __init__(self, message: str, status_code: Optional[int] = None, data: Optional[Dict[str, Any]] = None):
        super().__init__(message)
        self.status_code = status_code
        self.data = data or {}


class NagadClient:
    SANDBOX_URL = "http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/"
    PRODUCTION_URL = "https://api.mynagad.com/api/dfs/"

    def __init__(
        self,
        merchant_id: str,
        merchant_private_key: str,
        pg_public_key: str,
        sandbox: bool = False,
        server_ip: str = "127.0.0.1",
        timeout: int = 30
    ):
        if not HAS_CRYPTOGRAPHY:
            raise ImportError("NagadClient requires the 'cryptography' library. Run: pip install cryptography")

        if not merchant_id or not merchant_private_key or not pg_public_key:
            raise ValueError("merchant_id, merchant_private_key, and pg_public_key must all be provided.")

        self.merchant_id = merchant_id.strip()
        self.merchant_private_key_pem = self._format_private_key(merchant_private_key)
        self.pg_public_key_pem = self._format_public_key(pg_public_key)
        self.base_url = self.SANDBOX_URL if sandbox else self.PRODUCTION_URL
        self.server_ip = server_ip.strip()
        self.timeout = timeout

        self.private_key = load_pem_private_key(self.merchant_private_key_pem.encode(), password=None)
        self.pg_public_key = load_pem_public_key(self.pg_public_key_pem.encode())

        self.session = requests.Session()

    # ==========================================
    # Core Payment Operations
    # ==========================================

    def initialize_checkout(self, order_id: str, account_number: Optional[str] = None) -> Dict[str, str]:
        """Step 1: Exchange cryptographic challenge and acquire paymentReferenceId."""
        date_time = datetime.now().strftime("%Y%m%d%H%M%S")
        challenge = self.generate_challenge(40)

        sensitive_data = {
            "merchantId": self.merchant_id,
            "datetime": date_time,
            "orderId": order_id,
            "challenge": challenge
        }

        import json
        sensitive_json = json.dumps(sensitive_data, separators=(",", ":"))
        encrypted_sensitive_data = self.encrypt_with_pg_public_key(sensitive_json)
        signature = self.generate_signature(sensitive_json)

        payload = {
            "accountNumber": account_number,
            "dateTime": date_time,
            "sensitiveData": encrypted_sensitive_data,
            "signature": signature
        }

        url = f"{self.base_url}check-out/initialize/{self.merchant_id}/{order_id}"
        response = self._http_post(url, payload)

        if "reason" in response or "sensitiveData" not in response:
            msg = response.get("message", "Nagad Checkout Initialization Failed.")
            raise NagadException(f"Nagad Init Error: {msg}", data=response)

        decrypted_json = self.decrypt_with_private_key(response["sensitiveData"])
        try:
            decrypted_data = json.loads(decrypted_json)
        except Exception as e:
            raise NagadException(f"Failed to parse decrypted init response: {decrypted_json}") from e

        if "paymentReferenceId" not in decrypted_data:
            raise NagadException("Nagad Init Error: Missing paymentReferenceId in response.")

        if "challenge" in decrypted_data and decrypted_data["challenge"] != challenge:
            raise NagadException("Nagad Security Error: Cryptographic challenge mismatch.")

        return {
            "paymentReferenceId": decrypted_data["paymentReferenceId"],
            "challenge": decrypted_data.get("challenge", challenge)
        }

    def complete_checkout(
        self,
        payment_reference_id: str,
        order_id: str,
        amount: Union[float, str],
        callback_url: str,
        challenge: str
    ) -> Dict[str, Any]:
        """Step 2: Submit order amount and acquire hosted payment redirect URL."""
        import json
        sensitive_data_order = {
            "merchantId": self.merchant_id,
            "orderId": order_id,
            "currencyCode": "050",  # ISO 4217 numeric for BDT
            "amount": f"{float(amount):.2f}",
            "challenge": challenge
        }

        sensitive_json = json.dumps(sensitive_data_order, separators=(",", ":"))
        encrypted_sensitive_data = self.encrypt_with_pg_public_key(sensitive_json)
        signature = self.generate_signature(sensitive_json)

        payload = {
            "sensitiveData": encrypted_sensitive_data,
            "signature": signature,
            "merchantCallbackURL": callback_url
        }

        url = f"{self.base_url}check-out/complete/{payment_reference_id}"
        response = self._http_post(url, payload)

        if response.get("status") != "Success" or "callBackUrl" not in response:
            msg = response.get("message", str(response))
            raise NagadException(f"Nagad Checkout Complete Error: {msg}", data=response)

        return response

    def create_payment(
        self,
        order_id: str,
        amount: Union[float, str],
        callback_url: str,
        account_number: Optional[str] = None
    ) -> str:
        """High-level 1-step checkout helper returning hosted payment URL directly."""
        init = self.initialize_checkout(order_id, account_number)
        complete = self.complete_checkout(
            init["paymentReferenceId"],
            order_id,
            amount,
            callback_url,
            init["challenge"]
        )
        return complete["callBackUrl"]

    def verify_payment(self, payment_ref_id: str) -> Dict[str, Any]:
        """Step 4: Verify payment status with Nagad core engine."""
        url = f"{self.base_url}verify/payment/{payment_ref_id}"
        return self._http_get(url)

    @staticmethod
    def parse_callback(callback_input: Union[str, Dict[str, Any]]) -> Dict[str, str]:
        """Extract callback query parameters from redirect URL."""
        if isinstance(callback_input, dict):
            return callback_input

        parsed = urlparse(callback_input)
        qs = parse_qs(parsed.query if parsed.query else callback_input)
        return {k: v[0] for k, v in qs.items()}

    # ==========================================
    # Cryptographic Engine (PKCS#1 v1.5)
    # ==========================================

    def encrypt_with_pg_public_key(self, plain_text: str) -> str:
        cipher = self.pg_public_key.encrypt(
            plain_text.encode("utf-8"),
            padding.PKCS1v15()
        )
        return base64.b64encode(cipher).decode("utf-8")

    def generate_signature(self, plain_text: str) -> str:
        sig = self.private_key.sign(
            plain_text.encode("utf-8"),
            padding.PKCS1v15(),
            hashes.SHA256()
        )
        return base64.b64encode(sig).decode("utf-8")

    def decrypt_with_private_key(self, base64_crypto_text: str) -> str:
        raw_cipher = base64.b64decode(base64_crypto_text)
        decrypted = self.private_key.decrypt(
            raw_cipher,
            padding.PKCS1v15()
        )
        return decrypted.decode("utf-8")

    @staticmethod
    def generate_challenge(length: int = 40) -> str:
        return os.urandom(length // 2).hex()

    @staticmethod
    def _format_private_key(key: str) -> str:
        clean = re.sub(r"-----.*?-----|\s+", "", key)
        lines = [clean[i:i + 64] for i in range(0, len(clean), 64)]
        return "-----BEGIN RSA PRIVATE KEY-----\n" + "\n".join(lines) + "\n-----END RSA PRIVATE KEY-----"

    @staticmethod
    def _format_public_key(key: str) -> str:
        clean = re.sub(r"-----.*?-----|\s+", "", key)
        lines = [clean[i:i + 64] for i in range(0, len(clean), 64)]
        return "-----BEGIN PUBLIC KEY-----\n" + "\n".join(lines) + "\n-----END PUBLIC KEY-----"

    # ==========================================
    # HTTP Transport Engine
    # ==========================================

    def _http_post(self, url: str, payload: Dict[str, Any]) -> Dict[str, Any]:
        headers = {
            "Content-Type": "application/json",
            "X-KM-Api-Version": "v-0.2.0",
            "X-KM-IP-V4": self.server_ip,
            "X-KM-Client-Type": "PC_WEB",
            "Accept": "application/json"
        }
        res = self.session.post(url, json=payload, headers=headers, timeout=self.timeout)
        try:
            return res.json()
        except Exception:
            return {"raw": res.text, "status_code": res.status_code}

    def _http_get(self, url: str) -> Dict[str, Any]:
        headers = {
            "X-KM-Api-Version": "v-0.2.0",
            "X-KM-IP-V4": self.server_ip,
            "X-KM-Client-Type": "PC_WEB",
            "Accept": "application/json"
        }
        res = self.session.get(url, headers=headers, timeout=self.timeout)
        try:
            return res.json()
        except Exception:
            return {"raw": res.text, "status_code": res.status_code}
