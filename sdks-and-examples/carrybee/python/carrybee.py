"""
Carrybee Courier API v2.0 - Python SDK
Author: Refat Rahman (https://refat.ovh | @refatbd)
License: MIT
"""

import hmac
from typing import Any, Dict, List, Optional
import requests


class CarrybeeException(Exception):
    """Base exception for Carrybee API errors."""
    def __init__(self, message: str, status_code: Optional[int] = None, data: Optional[Dict[str, Any]] = None):
        super().__init__(message)
        self.status_code = status_code
        self.data = data or {}


class CarrybeeClient:
    PRODUCTION_URL = "https://developers.carrybee.com"
    SANDBOX_URL = "https://sandbox.carrybee.com"

    def __init__(
        self,
        client_id: str,
        client_secret: str,
        client_context: str,
        sandbox: bool = False,
        timeout: int = 30
    ):
        if not client_id or not client_secret or not client_context:
            raise ValueError("client_id, client_secret, and client_context must be provided.")

        self.client_id = client_id.strip()
        self.client_secret = client_secret.strip()
        self.client_context = client_context.strip()
        self.base_url = self.SANDBOX_URL if sandbox else self.PRODUCTION_URL
        self.timeout = timeout

        self.session = requests.Session()
        self.session.headers.update({
            "Client-ID": self.client_id,
            "Client-Secret": self.client_secret,
            "Client-Context": self.client_context,
            "Content-Type": "application/json",
            "Accept": "application/json"
        })

    # ==========================================
    # Location & Coverage Endpoints
    # ==========================================

    def get_cities(self) -> Dict[str, Any]:
        """Fetch list of all serviceable cities."""
        return self._request("GET", "/api/v2/cities")

    def get_zones(self, city_id: int) -> Dict[str, Any]:
        """Fetch all zones for a specific city ID."""
        return self._request("GET", f"/api/v2/zones/{city_id}")

    def get_area_suggestion(self, search: str) -> Dict[str, Any]:
        """Autocomplete search for recipient areas (min 3 chars)."""
        return self._request("GET", "/api/v2/area-suggestion", params={"search": search})

    def resolve_address_details(self, address: str) -> Dict[str, Any]:
        """Parse raw address string into city_id and zone_id."""
        return self._request("POST", "/api/v2/address-details", data={"address": address})

    # ==========================================
    # Store / Warehouse Endpoints
    # ==========================================

    def get_stores(self) -> Dict[str, Any]:
        """Fetch list of all merchant warehouse / pickup stores."""
        return self._request("GET", "/api/v2/stores")

    def create_store(self, store_data: Dict[str, Any]) -> Dict[str, Any]:
        """Create a new merchant warehouse / store location."""
        return self._request("POST", "/api/v2/stores", data=store_data)

    # ==========================================
    # Order Management Endpoints
    # ==========================================

    def create_order(self, order_data: Dict[str, Any]) -> Dict[str, Any]:
        """Create a single delivery parcel order."""
        return self._request("POST", "/api/v2/orders", data=order_data)

    def create_bulk_orders(self, orders: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Create multiple parcel orders in bulk (Async 202)."""
        return self._request("POST", "/api/v2/orders-bulk", data={"orders": orders})

    def track_order(self, consignment_id: str) -> Dict[str, Any]:
        """Query real-time parcel status by Consignment ID."""
        return self._request("GET", f"/api/v2/orders/track/{consignment_id}")

    def cancel_order(self, consignment_id: str, reason: str = "Merchant cancelled") -> Dict[str, Any]:
        """Cancel an unprocessed order."""
        return self._request("POST", "/api/v2/orders/cancel", data={
            "consignment_id": consignment_id,
            "reason": reason
        })

    # ==========================================
    # Reverse Pickup & Exchange Endpoints
    # ==========================================

    def create_reverse_pickup(self, pickup_data: Dict[str, Any]) -> Dict[str, Any]:
        """Initiate return collection from customer back to merchant."""
        return self._request("POST", "/api/v2/orders/reverse-pickup", data=pickup_data)

    def create_exchange_order(self, consignment_id: str, exchange_data: Dict[str, Any]) -> Dict[str, Any]:
        """Book a replacement delivery and collect the old parcel."""
        return self._request("POST", f"/api/v2/orders/{consignment_id}/exchange", data=exchange_data)

    # ==========================================
    # Webhook Handshake Verification Helper
    # ==========================================

    @staticmethod
    def verify_webhook_handshake(incoming_header: Optional[str], configured_secret: str) -> bool:
        """Constant-time comparison for webhook verification secret header."""
        if not incoming_header or not configured_secret:
            return False
        return hmac.compare_digest(incoming_header.strip(), configured_secret.strip())

    # ==========================================
    # Core HTTP Engine
    # ==========================================

    def _request(
        self,
        method: str,
        path: str,
        data: Optional[Dict[str, Any]] = None,
        params: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        url = f"{self.base_url.rstrip('/')}/{path.lstrip('/')}"
        try:
            response = self.session.request(
                method=method.upper(),
                url=url,
                json=data if data and method.upper() in ["POST", "PUT", "PATCH"] else None,
                params=params,
                timeout=self.timeout
            )
            try:
                res_json = response.json()
            except ValueError:
                res_json = {"raw": response.text}

            if not response.ok:
                err_msg = res_json.get("message", f"HTTP {response.status_code} Error")
                raise CarrybeeException(err_msg, status_code=response.status_code, data=res_json)

            return res_json

        except requests.RequestException as e:
            raise CarrybeeException(f"Connection failure to Carrybee: {str(e)}") from e
