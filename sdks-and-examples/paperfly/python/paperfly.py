"""
Paperfly Courier API v2 Python Client
Author: Refat Rahman <https://refat.ovh>
GitHub: https://github.com/refatbd
License: MIT
"""

from typing import Any, Dict, Optional
import requests
from requests.auth import HTTPBasicAuth


class PaperflyClient:
    BASE_URL = "https://api.paperfly.com.bd"
    DEFAULT_KEY = "Paperfly_~La?Rj73FcLm"

    def __init__(
        self,
        username: str,
        password: str,
        paperfly_key: Optional[str] = None,
        base_url: Optional[str] = None,
        timeout: int = 30,
    ):
        self.username = username.strip()
        self.password = password.strip()
        self.paperfly_key = paperfly_key or self.DEFAULT_KEY
        self.base_url = (base_url or self.BASE_URL).rstrip("/")
        self.timeout = timeout
        self.session = requests.Session()
        self.session.auth = HTTPBasicAuth(self.username, self.password)
        self.session.headers.update({
            "Content-Type": "application/json",
            "Accept": "application/json",
            "paperflykey": self.paperfly_key,
        })

    def create_order(self, order_payload: Dict[str, Any]) -> Dict[str, Any]:
        """Book a standard delivery order."""
        return self._request("POST", "/merchant/api/service/new_order_v2.php", json=order_payload)

    def create_exchange_order(self, order_payload: Dict[str, Any]) -> Dict[str, Any]:
        """Book an exchange delivery order."""
        order_payload["orderType"] = "Exchange"
        return self._request("POST", "/merchant/api/service/new_order_v2.php", json=order_payload)

    def track_order(self, reference_number: str) -> Dict[str, Any]:
        """Track order milestone history."""
        return self._request("POST", "/API-Order-Tracking", json={"ReferenceNumber": reference_number})

    def cancel_order(self, order_id: str) -> Dict[str, Any]:
        """Cancel an unpicked order."""
        return self._request("POST", "/api/v1/cancel-order", json={"order_id": order_id})

    def _request(self, method: str, endpoint: str, **kwargs) -> Dict[str, Any]:
        url = f"{self.base_url}{endpoint}"
        kwargs.setdefault("timeout", self.timeout)

        response = self.session.request(method, url, **kwargs)
        try:
            data = response.json()
        except Exception:
            data = {"raw": response.text}

        if not response.ok:
            msg = data.get("message") or data.get("error") or f"HTTP {response.status_code}"
            raise requests.HTTPError(f"Paperfly API Error [{response.status_code}]: {msg}", response=response)

        return data
