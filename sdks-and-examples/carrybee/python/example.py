"""
Carrybee Courier API v2.0 - Python SDK Usage Example
Author: Refat Rahman (https://refat.ovh | @refatbd)
"""

import os
import time
from carrybee import CarrybeeClient, CarrybeeException


def main():
    client_id = os.getenv("CARRYBEE_CLIENT_ID", "YOUR_CLIENT_ID")
    client_secret = os.getenv("CARRYBEE_CLIENT_SECRET", "YOUR_CLIENT_SECRET")
    client_context = os.getenv("CARRYBEE_CLIENT_CONTEXT", "YOUR_CLIENT_CONTEXT")

    # Initialize client in Sandbox
    client = CarrybeeClient(
        client_id=client_id,
        client_secret=client_secret,
        client_context=client_context,
        sandbox=True
    )

    try:
        print("=== 1. Fetching Cities ===")
        cities = client.get_cities()
        city_list = cities.get("data", [])
        print(f"Total Cities: {len(city_list)}")
        if city_list:
            print(f"First City: {city_list[0].get('name')} (ID: {city_list[0].get('id')})")

        print("\n=== 2. Autocomplete Area Search ===")
        areas = client.get_area_suggestion("Mirpur")
        print("Area Suggestion Response:", areas)

        print("\n=== 3. Creating Single Order ===")
        order = client.create_order({
            "store_id": 101,
            "merchant_order_id": f"INV-PY-{int(time.time())}",
            "recipient_name": "Refat Rahman",
            "recipient_phone": "01811223344",
            "recipient_address": "House 14, Road 3, Dhanmondi, Dhaka",
            "recipient_city": 1,
            "recipient_zone": 5,
            "delivery_type": 1,
            "product_type": 1,
            "item_weight": 1100,
            "item_description": "Python Automated Logistics Pack",
            "item_quantity": 1,
            "collectable_amount": 1650,
            "special_instruction": "Call recipient before dispatching"
        })
        print("Created Order Response:", order)

        consignment_id = order.get("consignment_id")
        if consignment_id:
            print(f"\n=== 4. Tracking Consignment: {consignment_id} ===")
            tracking = client.track_order(consignment_id)
            print("Tracking Status:", tracking.get("status"))

    except CarrybeeException as e:
        print(f"API Error ({e.status_code}): {e}")
        print("Response Details:", e.data)


if __name__ == "__main__":
    main()
