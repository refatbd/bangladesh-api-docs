"""
RedX Delivery OpenAPI v1.0.0-beta Python Client
Author: Refat Rahman <https://refat.ovh>
GitHub: https://github.com/refatbd
License: MIT
"""

from typing import Any, Dict, Optional
import requests


class RedxClient:
    SANDBOX_URL = "https://sandbox.redx.com.bd/v1.0.0-beta"
    PRODUCTION_URL = "https://openapi.redx.com.bd/v1.0.0-beta"

    def __init__(self, jwt_token: str, is_sandbox: bool = False, timeout: int = 30):
        if not jwt_token:
            raise ValueError("RedX JWT token is required")
        self.jwt_token = jwt_token.strip()
        self.base_url = self.SANDBOX_URL if is_sandbox else self.PRODUCTION_URL
        self.timeout = timeout
        self.session = requests.Session()
        self.session.headers.update({
            "API-ACCESS-TOKEN": f"Bearer {self.jwt_token}",
            "Accept": "application/json",
        })

    def _request(self, method: str, endpoint: str, **kwargs) -> Dict[str, Any]:
        url = f"{self.base_url}{endpoint}"
        kwargs.setdefault("timeout", self.timeout)
        response = self.session.request(method, url, **kwargs)
        try:
            data = response.json()
        except Exception:
            data = {"raw": response.text}

        if not response.ok:
            error_msg = data.get("message") or data.get("error") or f"HTTP {response.status_code}"
            raise requests.HTTPError(f"RedX API Error [{response.status_code}]: {error_msg}", response=response)

        return data

    def track_parcel(self, tracking_id: str) -> Dict[str, Any]:
        """Track parcel transit timeline."""
        return self._request("GET", f"/parcel/track/{tracking_id}")

    def get_parcel_info(self, tracking_id: str) -> Dict[str, Any]:
        """Get full parcel details."""
        return self._request("GET", f"/parcel/info/{tracking_id}")

    def create_parcel(self, payload: Dict[str, Any]) -> Dict[str, Any]:
        """Create new consignment."""
        return self._request("POST", "/parcel", json=payload)

    def update_parcel(self, tracking_id: str, property_name: str, new_value: str, reason: str = "") -> Dict[str, Any]:
        """Update parcel property or cancel."""
        body = {
            "entity_type": "parcel-tracking-id",
            "entity_id": tracking_id,
            "update_details": {
                "property_name": property_name,
                "new_value": new_value,
                "reason": reason,
            }
        }
        return self._request("PATCH", "/parcels", json=body)

    def cancel_parcel(self, tracking_id: str, reason: str = "Merchant cancellation") -> Dict[str, Any]:
        """Cancel parcel booking."""
        return self.update_parcel(tracking_id, "status", "cancelled", reason)

    def get_areas(self) -> Dict[str, Any]:
        """Get all deliverable areas."""
        return self._request("GET", "/areas")

    def get_areas_by_post_code(self, post_code: int) -> Dict[str, Any]:
        """Filter areas by postal code."""
        return self._request("GET", "/areas", params={"post_code": post_code})

    def get_areas_by_district(self, district_name: str) -> Dict[str, Any]:
        """Filter areas by district name."""
        return self._request("GET", "/areas", params={"district_name": district_name})

    def create_pickup_store(self, name: str, phone: str, address: str, area_id: int) -> Dict[str, Any]:
        """Register new warehouse pickup location."""
        return self._request("POST", "/pickup/store", json={
            "name": name,
            "phone": phone,
            "address": address,
            "area_id": area_id
        })

    def get_pickup_stores(self) -> Dict[str, Any]:
        """Get list of pickup stores."""
        return self._request("GET", "/pickup/stores")

    def get_pickup_store_info(self, store_id: int) -> Dict[str, Any]:
        """Get single pickup store details."""
        return self._request("GET", f"/pickup/store/info/{store_id}")

    def calculate_charge(self, delivery_area_id: int, pickup_area_id: int, cash_collection_amount: float, weight: float) -> Dict[str, Any]:
        """Calculate delivery & COD charges."""
        params = {
            "delivery_area_id": delivery_area_id,
            "pickup_area_id": pickup_area_id,
            "cash_collection_amount": cash_collection_amount,
            "weight": weight,
        }
        return self._request("GET", "/charge/charge_calculator", params=params)
