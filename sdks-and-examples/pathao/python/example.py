"""
Pathao Courier Python Integration Example
"""

import os
from pathao import PathaoClient

client = PathaoClient(
    client_id=os.getenv("PATHAO_CLIENT_ID", "7N1aMJQbWm"),
    client_secret=os.getenv("PATHAO_CLIENT_SECRET", "wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39"),
    username=os.getenv("PATHAO_USERNAME", "test@pathao.com"),
    password=os.getenv("PATHAO_PASSWORD", "lovePathao"),
    is_sandbox=True,
)

print("=== Pathao Courier Python Demo ===\n")

try:
    print("1. Fetching Cities...")
    cities = client.get_cities()
    print(f"Total Cities: {len(cities.get('data', {}).get('data', []))}")

    print("\n2. Estimating delivery cost...")
    price = client.calculate_price({
        "store_id": 4821,
        "item_type": 2,
        "delivery_type": 48,
        "item_weight": 0.5,
        "recipient_city": 1,
        "recipient_zone": 1070
    })
    print(f"Final Delivery Fee: BDT {price.get('data', {}).get('final_price')}")

    print("\n3. Placing test order...")
    order = client.create_order({
        "store_id": 4821,
        "merchant_order_id": "ORD-PY-8831",
        "recipient_name": "Shakil Ahmed",
        "recipient_phone": "01711223344",
        "recipient_address": "House 5, Road 2, Sector 10, Uttara, Dhaka",
        "recipient_city": 1,
        "recipient_zone": 1070,
        "recipient_area": 37,
        "delivery_type": 48,
        "item_type": 2,
        "item_quantity": 1,
        "item_weight": 0.5,
        "amount_to_collect": 1400,
        "item_description": "Smart Watch"
    })
    print("Booking Successful! Consignment ID:", order.get("data", {}).get("consignment_id"))

except Exception as e:
    print("Error:", e)
