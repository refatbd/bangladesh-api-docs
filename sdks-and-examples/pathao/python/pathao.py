"""
Pathao Courier Aladdin API v1 Python Client
Author: Refat Rahman <https://refat.ovh>
GitHub: https://github.com/refatbd
License: MIT
"""

from typing import Any, Dict, List, Optional
import requests


class PathaoClient:
    SANDBOX_URL = "https://courier-api-sandbox.pathao.com"
    PRODUCTION_URL = "https://api-hermes.pathao.com"

    def __init__(
        self,
        client_id: str,
        client_secret: str,
        username: str,
        password: str,
        is_sandbox: bool = False,
        cached_access_token: Optional[str] = None,
        cached_refresh_token: Optional[str] = None,
        timeout: int = 30,
    ):
        self.client_id = client_id.strip()
        self.client_secret = client_secret.strip()
        self.username = username.strip()
        self.password = password.strip()
        self.base_url = self.SANDBOX_URL if is_sandbox else self.PRODUCTION_URL
        self.access_token = cached_access_token
        self.refresh_token = cached_refresh_token
        self.timeout = timeout
        self.session = requests.Session()

    def issue_token(self) -> Dict[str, Any]:
        """Request new access token using password grant."""
        payload = {
            "client_id": self.client_id,
            "client_secret": self.client_secret,
            "grant_type": "password",
            "username": self.username,
            "password": self.password,
        }
        data = self._raw_request("POST", "/aladdin/api/v1/issue-token", json=payload)
        if "access_token" in data:
            self.access_token = data["access_token"]
            self.refresh_token = data.get("refresh_token")
        return data

    def renew_token(self) -> Dict[str, Any]:
        """Renew access token using refresh_token grant."""
        if not self.refresh_token:
            return self.issue_token()

        payload = {
            "client_id": self.client_id,
            "client_secret": self.client_secret,
            "grant_type": "refresh_token",
            "refresh_token": self.refresh_token,
        }
        data = self._raw_request("POST", "/aladdin/api/v1/issue-token", json=payload)
        if "access_token" in data:
            self.access_token = data["access_token"]
            self.refresh_token = data.get("refresh_token", self.refresh_token)
        return data

    def get_access_token(self) -> str:
        """Get or automatically acquire access token."""
        if not self.access_token:
            self.issue_token()
        return str(self.access_token)

    def get_cities(self) -> Dict[str, Any]:
        """Get list of cities."""
        return self._auth_request("GET", "/aladdin/api/v1/city-list")

    def get_zones(self, city_id: int) -> Dict[str, Any]:
        """Get zones in city."""
        return self._auth_request("GET", f"/aladdin/api/v1/cities/{city_id}/zone-list")

    def get_areas(self, zone_id: int) -> Dict[str, Any]:
        """Get areas in zone."""
        return self._auth_request("GET", f"/aladdin/api/v1/zones/{zone_id}/area-list")

    def create_store(self, store_payload: Dict[str, Any]) -> Dict[str, Any]:
        """Create a new merchant warehouse."""
        return self._auth_request("POST", "/aladdin/api/v1/stores", json=store_payload)

    def get_stores(self) -> Dict[str, Any]:
        """List registered merchant stores."""
        return self._auth_request("GET", "/aladdin/api/v1/stores")

    def create_order(self, order_payload: Dict[str, Any]) -> Dict[str, Any]:
        """Place single parcel order."""
        return self._auth_request("POST", "/aladdin/api/v1/orders", json=order_payload)

    def create_bulk_orders(self, orders: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Place bulk orders array."""
        return self._auth_request("POST", "/aladdin/api/v1/orders/bulk", json={"orders": orders})

    def get_order_info(self, consignment_id: str) -> Dict[str, Any]:
        """Query order status summary."""
        return self._auth_request("GET", f"/aladdin/api/v1/orders/{consignment_id}/info")

    def calculate_price(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        """Estimate delivery pricing plan."""
        return self._auth_request("POST", "/aladdin/api/v1/merchant/price-plan", json=payload)

    def _auth_request(self, method: str, endpoint: str, **kwargs) -> Dict[str, Any]:
        token = self.get_access_token()
        headers = kwargs.pop("headers", {})
        headers["Authorization"] = f"Bearer {token}"

        try:
            return self._raw_request(method, endpoint, headers=headers, **kwargs)
        except requests.HTTPError as e:
            if e.response is not None and e.response.status_code == 401:
                self.renew_token()
                token = self.get_access_token()
                headers["Authorization"] = f"Bearer {token}"
                return self._raw_request(method, endpoint, headers=headers, **kwargs)
            raise e

    def _raw_request(self, method: str, endpoint: str, **kwargs) -> Dict[str, Any]:
        url = f"{self.base_url}{endpoint}"
        kwargs.setdefault("timeout", self.timeout)
        headers = kwargs.pop("headers", {})
        headers.setdefault("Content-Type", "application/json; charset=UTF-8")
        headers.setdefault("Accept", "application/json")

        response = self.session.request(method, url, headers=headers, **kwargs)
        try:
            data = response.json()
        except Exception:
            data = {"raw": response.text}

        if not response.ok:
            msg = data.get("message") or data.get("error") or f"HTTP {response.status_code}"
            raise requests.HTTPError(f"Pathao API Error [{response.status_code}]: {msg}", response=response)

        return data
