"""
Paperfly Python Client Example
"""

import os
import time
from paperfly import PaperflyClient

client = PaperflyClient(
    username=os.getenv("PAPERFLY_USERNAME", "sellerone"),
    password=os.getenv("PAPERFLY_PASSWORD", "YOUR_PASSWORD"),
)

print("=== Paperfly Python Integration Demo ===\n")

try:
    ref = f"ORD-{int(time.time())}"
    print(f"1. Creating order {ref}...")
    order = client.create_order({
        "merchantOrderReference": ref,
        "storeName": "Apex Outlet",
        "productBrief": "Casual Sneakers",
        "packagePrice": "2200",
        "max_weight": "0.7",
        "customerName": "Liton Ovi",
        "customerAddress": "Banani Road 11, Dhaka",
        "customerPhone": "01610202717"
    })
    print("Order Response:", order)

    print("\n2. Tracking order...")
    tracking = client.track_order(ref)
    print("Tracking:", tracking)

except Exception as e:
    print("Error:", e)
