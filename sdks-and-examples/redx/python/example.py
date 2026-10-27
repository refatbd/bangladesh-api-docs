"""
RedX Python Client Example
"""

import os
from redx import RedxClient

token = os.getenv("REDX_JWT_TOKEN", "YOUR_SANDBOX_JWT_TOKEN")
client = RedxClient(token, is_sandbox=True)

print("=== RedX Python Integration Demo ===\n")

try:
    print("1. Estimating delivery cost...")
    cost = client.calculate_charge(delivery_area_id=12, pickup_area_id=1, cash_collection_amount=1500, weight=500)
    print(f"Delivery: BDT {cost.get('deliveryCharge')}, COD: BDT {cost.get('codCharge')}")

    print("\n2. Discovering areas for postal code 1206...")
    areas = client.get_areas_by_post_code(1206)
    print(f"Found {len(areas.get('areas', []))} sub-areas.")

    print("\n3. Booking a parcel...")
    parcel = client.create_parcel({
        "customer_name": "Sabbir Hossain",
        "customer_phone": "01911223344",
        "delivery_area": "Mirpur DOHS",
        "delivery_area_id": 12,
        "customer_address": "House 10, Road 4, Mirpur DOHS, Dhaka",
        "merchant_invoice_id": "INV-PY-9921",
        "cash_collection_amount": "1200",
        "paid_return_charge": 0,
        "parcel_weight": 500,
        "instruction": "Urgent delivery",
        "value": 1200,
        "is_closed_box": True,
        "pickup_store_id": 1,
        "parcel_details_json": [
            {"name": "USB-C Fast Charger", "category": "Electronics", "value": 1200}
        ]
    })
    print(f"Booking Success! Tracking ID: {parcel.get('tracking_id')}")

except Exception as e:
    print("Error:", e)
