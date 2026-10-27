"""
Production-Ready Python 3 Client for Steadfast Courier API (v1)

Authored & Maintained by Refat Rahman (https://github.com/refatbd)
Website: https://refat.ovh

License: MIT
"""

import json
import re
import urllib.parse
import urllib.request
from typing import Any, Dict, List, Optional


class SteadfastClient:
    DEFAULT_BASE_URL = "https://portal.packzy.com/api/v1"

    def __init__(
        self,
        api_key: str,
        secret_key: str,
        base_url: Optional[str] = None,
        timeout: int = 20,
    ):
        self.api_key = api_key.strip()
        self.secret_key = secret_key.strip()
        self.base_url = (base_url or self.DEFAULT_BASE_URL).rstrip("/")
        self.timeout = timeout

    def ping(self) -> Dict[str, Any]:
        """Diagnostic gateway ping (no auth needed)"""
        return self._request("GET", "/ping", with_auth=False)

    def get_balance(self) -> Dict[str, Any]:
        """Fetch current COD wallet balance"""
        return self._request("GET", "/get_balance")

    def create_order(
        self,
        invoice: str,
        recipient_name: str,
        recipient_phone: str,
        recipient_address: str,
        cod_amount: float,
        note: str = "",
    ) -> Dict[str, Any]:
        """Book a single parcel with character sanitization"""

        def sanitize(text: str, max_len: int) -> str:
            if not text:
                return ""
            cleaned = re.sub(r"[{}\;<>\\$]", " ", str(text)).strip()
            return cleaned[:max_len]

        payload = {
            "invoice": sanitize(invoice, 100),
            "recipient_name": sanitize(recipient_name, 100),
            "recipient_phone": sanitize(recipient_phone, 40),
            "recipient_address": sanitize(recipient_address, 490),
            "cod_amount": max(0.0, float(cod_amount)),
            "note": sanitize(note, 480),
        }
        return self._request("POST", "/create_order", payload)

    def create_bulk_order(self, orders: List[Dict[str, Any]], extended: bool = False) -> Dict[str, Any]:
        """Bulk book up to 500 parcels"""
        endpoint = "/create_order/bulk-order/extended" if extended else "/create_order/bulk-order"
        return self._request("POST", endpoint, {"data": orders})

    def get_status_by_cid(self, consignment_id: int | str) -> Dict[str, Any]:
        """Status by Steadfast Consignment ID"""
        return self._request("GET", f"/status_by_cid/{consignment_id}")

    def get_status_with_return(self, consignment_id: int | str) -> Dict[str, Any]:
        """Status with return progress"""
        return self._request("GET", f"/status_with_return_status_by_cid/{consignment_id}")

    def get_status_by_invoice(self, invoice: str) -> Dict[str, Any]:
        """Status by merchant invoice"""
        encoded = urllib.parse.quote(invoice)
        return self._request("GET", f"/status_by_invoice/{encoded}")

    def get_status_by_tracking_code(self, tracking_code: str) -> Dict[str, Any]:
        """Status by tracking code"""
        encoded = urllib.parse.quote(tracking_code)
        return self._request("GET", f"/status_by_trackingcode/{encoded}")

    def get_tracking_timeline(self, invoice: str) -> Dict[str, Any]:
        """Full tracking audit history"""
        encoded = urllib.parse.quote(invoice)
        return self._request("GET", f"/trackings_by_invoice/{encoded}")

    def create_pickup_request(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        """Request rider collection"""
        return self._request("POST", "/create_pickup_request", payload)

    def create_return_request(self, consignment_id: int | str, reason: str) -> Dict[str, Any]:
        """Request parcel recall"""
        return self._request("POST", "/create_return_request", {
            "consignment_id": consignment_id,
            "reason": reason,
        })

    def get_return_requests(self, page: int = 1) -> Dict[str, Any]:
        """Paginated list of return requests"""
        return self._request("GET", f"/get_return_requests?page={page}")

    def get_payments(self, page: int = 1) -> Dict[str, Any]:
        """Paginated payout settlements"""
        return self._request("GET", f"/payments?page={page}")

    def get_payment_details(self, payment_id: int | str) -> Dict[str, Any]:
        """Detailed settlement breakdown"""
        return self._request("GET", f"/payments/{payment_id}")

    def get_police_stations(self) -> Dict[str, Any]:
        """List covered thanas / police stations"""
        return self._request("GET", "/police_stations")

    def check_fraud_score(self, phone: str) -> Dict[str, Any]:
        """Check customer delivery risk score"""
        clean_phone = re.sub(r"\D", "", phone)
        return self._request("GET", f"/fraud_check/score/{clean_phone}")

    def _request(
        self,
        method: str,
        path: str,
        payload: Optional[Dict[str, Any]] = None,
        with_auth: bool = True,
    ) -> Dict[str, Any]:
        url = f"{self.base_url}{path}"
        headers = {
            "Accept": "application/json",
            "User-Agent": "Steadfast-Python-Client/1.0",
        }

        if with_auth:
            headers["Api-Key"] = self.api_key
            headers["Secret-Key"] = self.secret_key

        data_bytes = None
        if payload is not None and method.upper() in ["POST", "PUT", "PATCH"]:
            headers["Content-Type"] = "application/json"
            data_bytes = json.dumps(payload).encode("utf-8")

        req = urllib.request.Request(url, data=data_bytes, headers=headers, method=method.upper())

        try:
            with urllib.request.urlopen(req, timeout=self.timeout) as resp:
                status_code = resp.getcode()
                body = resp.read().decode("utf-8")
                parsed = json.loads(body) if body else {}
                return {
                    "success": True,
                    "status_code": status_code,
                    "data": parsed,
                }
        except urllib.error.HTTPError as e:
            err_body = e.read().decode("utf-8")
            parsed_err = json.loads(err_body) if err_body else {}
            return {
                "success": False,
                "status_code": e.code,
                "data": parsed_err,
                "message": e.reason,
            }
        except Exception as e:
            return {
                "success": False,
                "status_code": 0,
                "message": str(e),
            }
