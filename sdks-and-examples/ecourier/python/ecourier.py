"""
eCourier Courier API v5.5 - Python SDK
Author: Refat Rahman (https://refat.ovh | @refatbd)
License: MIT
"""

from typing import Any, Dict, List, Optional, Union
import requests


class EcourierException(Exception):
    """Base exception for eCourier API communication errors."""
    def __init__(self, message: str, status_code: Optional[int] = None, data: Optional[Dict[str, Any]] = None):
        super().__init__(message)
        self.status_code = status_code
        self.data = data or {}


class EcourierClient:
    PRODUCTION_URL = "https://backoffice.ecourier.com.bd/api"
    STAGING_URL = "https://staging.ecourier.com.bd/api"

    def __init__(
        self,
        api_key: str,
        api_secret: str,
        user_id: str,
        sandbox: bool = False,
        timeout: int = 30
    ):
        if not api_key or not api_secret or not user_id:
            raise ValueError("api_key, api_secret, and user_id must all be provided.")

        self.api_key = api_key.strip()
        self.api_secret = api_secret.strip()
        self.user_id = user_id.strip()
        self.base_url = self.STAGING_URL if sandbox else self.PRODUCTION_URL
        self.timeout = timeout

        self.session = requests.Session()
        self.session.headers.update({
            "API-KEY": self.api_key,
            "API-SECRET": self.api_secret,
            "USER-ID": self.user_id,
            "Content-Type": "application/json",
            "Accept": "application/json"
        })

    # ==========================================
    # 1. Geographic & Coverage Endpoints
    # ==========================================

    def get_cities(self) -> List[Dict[str, Any]]:
        """Fetch all serviceable cities."""
        return self._request("/city-list")

    def get_thanas(self, city: str) -> Dict[str, Any]:
        """Fetch thanas under a specific city."""
        return self._request("/thana-list", data={"city": city})

    def get_areas_by_postcode(self, postcode: str) -> Dict[str, Any]:
        """Fetch areas under a specific postal code."""
        return self._request("/area-list", data={"postcode": postcode})

    def get_postcodes(self, city: str, thana: str) -> Dict[str, Any]:
        """Fetch postcodes for a city and thana."""
        return self._request("/postcode-list", data={"city": city, "thana": thana})

    def get_areas_by_district(self, district: str) -> Dict[str, Any]:
        """Query all areas and hub IDs under an entire district."""
        return self._request("/area-by-district", data={"district": district})

    def get_branches(self) -> Dict[str, Any]:
        """Fetch list of all eCourier branch offices and hubs."""
        return self._request("/branch-list")

    def get_hub_info_by_area(self, area: str) -> Dict[str, Any]:
        """Lookup destination delivery hub info by area name."""
        return self._request("/hub-info-by-area", data={"area": area})

    # ==========================================
    # 2. Package Management Endpoints
    # ==========================================

    def get_packages(self) -> List[Dict[str, Any]]:
        """Fetch assigned packages and contracted rates for merchant."""
        return self._request("/packages")

    # ==========================================
    # 3. Order Placement & Reseller Endpoints
    # ==========================================

    def place_order(self, order_data: Dict[str, Any]) -> Dict[str, Any]:
        """Place a single delivery or exchange order."""
        return self._request("/order-place", data=order_data)

    def place_reseller_order(self, reseller_data: Dict[str, Any]) -> Dict[str, Any]:
        """Place multi-vendor reseller order with automatic child payout routing."""
        return self._request("/order-place-reseller", data=reseller_data)

    def cancel_order(self, tracking: str, comment: str = "Merchant cancelled") -> Dict[str, Any]:
        """Cancel pending consignment prior to rider pickup."""
        return self._request("/cancel-order", data={"tracking": tracking, "comment": comment})

    def cancel_child_order(self, tracking: str, comment: str = "Child partner cancelled") -> Dict[str, Any]:
        """Cancel child consignment prior to pickup."""
        return self._request("/cancel-order-child", data={"tracking": tracking, "comment": comment})

    # ==========================================
    # 4. Milestone Tracking & Invoice Labels
    # ==========================================

    def track(self, params: Dict[str, str]) -> Dict[str, Any]:
        """Query parcel milestone history by ecr or product_id."""
        return self._request("/track", data=params)

    def track_by_ecr(self, ecr: str) -> Dict[str, Any]:
        """Shortcut to track by eCourier ECR tracking number."""
        return self.track({"ecr": ecr})

    def track_child(self, ecr: str) -> Dict[str, Any]:
        """Query tracking history for a child vendor parcel."""
        return self._request("/track-child", data={"ecr": ecr})

    def get_label_print_url(self, tracking: str) -> Dict[str, Any]:
        """Generate thermal barcode label PDF URL."""
        return self._request("/label-print", data={"tracking": tracking})

    def get_payment_status(self, tracking: str) -> Dict[str, Any]:
        """Reconcile COD collection and fee deduction breakdown."""
        return self._request("/payment-status", data={"tracking": tracking})

    # ==========================================
    # 5. Fraud Alert & Booster VAS Services
    # ==========================================

    def check_fraud_status(self, phone_number: str) -> Dict[str, Any]:
        """Query customer fraud return history and risk profile."""
        return self._request("/fraud-status-check", data={"number": phone_number})

    def send_booster_sms(self, sms_data: Dict[str, Any]) -> Dict[str, Any]:
        """Broadcast promotional or transactional SMS via Booster gateway."""
        return self._request("/bs-sms", data=sms_data)

    def send_booster_top_up(self, number: str, amount: int, operator_type: int = 1) -> Dict[str, Any]:
        """Top-up mobile airtime deducted from merchant COD balance."""
        return self._request("/bs-top", data={
            "number": number,
            "amount": amount,
            "operator_type": operator_type
        })

    def get_booster_top_up_status(self, transaction_id: str) -> Dict[str, Any]:
        """Check airtime recharge transaction status."""
        return self._request("/bs-top-status", data={"transaction_id": transaction_id})

    def send_booster_otp(self, number: str, otp_body: str, service_type: str = "mask") -> Dict[str, Any]:
        """Dispatch high-priority OTP verification SMS."""
        return self._request("/bs-top-otp", data={
            "number": number,
            "op_title": "booster",
            "text_type": "text",
            "op_body": otp_body,
            "service_type": service_type
        })

    # ==========================================
    # Core HTTP Engine (Strict POST Enforcement)
    # ==========================================

    def _request(self, path: str, data: Optional[Dict[str, Any]] = None) -> Any:
        url = f"{self.base_url.rstrip('/')}/{path.lstrip('/')}"
        try:
            response = self.session.post(
                url=url,
                json=data or {},
                timeout=self.timeout
            )
            try:
                res_json = response.json()
            except ValueError:
                res_json = {"raw": response.text}

            if not response.ok:
                err_msg = f"HTTP {response.status_code} Error"
                if isinstance(res_json, dict):
                    if "errors" in res_json and isinstance(res_json["errors"], list):
                        err_msg += " - " + "; ".join(filter(None, res_json["errors"]))
                    elif "message" in res_json:
                        err_msg += f" - {res_json['message']}"
                raise EcourierException(err_msg, status_code=response.status_code, data=res_json)

            return res_json

        except requests.RequestException as e:
            raise EcourierException(f"Connection error to eCourier: {str(e)}") from e
