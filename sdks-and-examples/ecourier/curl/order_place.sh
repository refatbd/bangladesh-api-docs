#!/usr/bin/env bash
# eCourier Courier API v5.5 - Place Single Order
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${ECOURIER_BASE_URL:-https://staging.ecourier.com.bd/api}"
API_KEY="${ECOURIER_API_KEY:-YOUR_API_KEY}"
API_SECRET="${ECOURIER_API_SECRET:-YOUR_API_SECRET}"
USER_ID="${ECOURIER_USER_ID:-YOUR_USER_ID}"

curl -s -X POST "${BASE_URL}/order-place" \
  -H "API-KEY: ${API_KEY}" \
  -H "API-SECRET: ${API_SECRET}" \
  -H "USER-ID: ${USER_ID}" \
  -H "Content-Type: application/json" \
  -d '{
    "recipient_name": "Tanvir Ahmed",
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
    "product_id": "INV-2026-EC-001",
    "comments": "Fragile accessory item",
    "is_fragile": 1,
    "sending_type": 1,
    "is_ipay": 0
  }' | jq .
