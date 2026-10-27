"""
Nagad Payment Gateway (API v3.3) - Python SDK Example
Author: Refat Rahman (https://refat.ovh | @refatbd)
"""

import os
import time
from nagad import NagadClient, NagadException


def main():
    merchant_id = os.getenv("NAGAD_MERCHANT_ID", "6800000025")
    private_key = os.getenv("NAGAD_MERCHANT_PRIVATE_KEY", "YOUR_PRIVATE_KEY")
    pg_public_key = os.getenv("NAGAD_PG_PUBLIC_KEY", "YOUR_PG_PUBLIC_KEY")
    server_ip = os.getenv("NAGAD_SERVER_IP", "103.145.118.24")

    # Initialize client in Sandbox
    try:
        client = NagadClient(
            merchant_id=merchant_id,
            merchant_private_key=private_key,
            pg_public_key=pg_public_key,
            sandbox=True,
            server_ip=server_ip
        )
    except Exception as e:
        print(f"Client Init Note (Credentials pending): {e}")
        return

    order_id = f"INV-PY-{int(time.time())}"
    amount = 500.00
    callback_url = "https://merchant.example.com/payment/nagad/callback"

    print("=== 1. High-Level 1-Step Payment Creation ===")
    print(f"Creating payment for Order: {order_id} (BDT {amount})...")

    # In a live environment with valid keys:
    # redirect_url = client.create_payment(order_id, amount, callback_url)
    # print("Redirect customer to:", redirect_url)

    print("\n=== 2. Parsing Inbound Callback ===")
    sample_url = f"https://merchant.example.com/callback?merchant={merchant_id}&order_id={order_id}&payment_ref_id=SAMPLE_REF_ID&status=Success&status_code=00_0000_000"
    params = NagadClient.parse_callback(sample_url)
    print("Parsed Callback Query:", params)

    print("\n=== 3. Server Verification ===")
    # status = client.verify_payment(params["payment_ref_id"])
    # print("Verified Result:", status)

    print("Nagad Python SDK loaded successfully.")


if __name__ == "__main__":
    main()
