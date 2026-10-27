"""
eCourier Courier API v5.5 - Python SDK Example
Author: Refat Rahman (https://refat.ovh | @refatbd)
"""

import os
import time
from ecourier import EcourierClient, EcourierException


def main():
    api_key = os.getenv("ECOURIER_API_KEY", "YOUR_API_KEY")
    api_secret = os.getenv("ECOURIER_API_SECRET", "YOUR_API_SECRET")
    user_id = os.getenv("ECOURIER_USER_ID", "YOUR_USER_ID")

    client = EcourierClient(
        api_key=api_key,
        api_secret=api_secret,
        user_id=user_id,
        sandbox=True
    )

    try:
        print("=== 1. Checking Merchant Package List ===")
        packages = client.get_packages()
        print(f"Total Packages: {len(packages)}")
        if packages:
            print(f"Primary Tier: {packages[0].get('package_name')} ({packages[0].get('package_code')})")

        print("\n=== 2. AI Fraud Assessment on Customer ===")
        fraud = client.check_fraud_status("01811223344")
        print("Fraud Risk Status:", fraud.get("customer_status"))

        print("\n=== 3. Booking Single Consignment ===")
        order = client.place_order({
            "recipient_name": "Refat Rahman",
            "recipient_mobile": "01811223344",
            "recipient_city": "Dhaka",
            "recipient_thana": "Badda",
            "recipient_area": "Middle Badda",
            "recipient_address": "House 14, Road 3, Block D",
            "recipient_zip": "1212",
            "package_code": "#2431",
            "product_price": 1850,
            "payment_method": "COD",
            "parcel_type": "BOX",
            "product_id": f"INV-PY-{int(time.time())}",
            "comments": "Automated delivery via Python SDK",
            "is_fragile": 0,
            "sending_type": 1,
            "is_ipay": 0
        })
        print("Order Response:", order)

        tracking_id = order.get("ID")
        if tracking_id:
            print(f"\n=== 4. Tracking Consignment: {tracking_id} ===")
            tracking = client.track_by_ecr(tracking_id)
            print("Tracking Timeline Status:", tracking.get("message"))

            print("\n=== 5. Fetching Shipping Label URL ===")
            label = client.get_label_print_url(tracking_id)
            print("Downloadable PDF:", label.get("query_data"))

    except EcourierException as e:
        print(f"API Exception ({e.status_code}): {e}")
        print("Details:", e.data)


if __name__ == "__main__":
    main()
