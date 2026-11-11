#!/usr/bin/env bash
# ==============================================================================
# Pathao Courier API - Create Single Order
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PATHAO_BASE_URL:-https://courier-api-sandbox.pathao.com}"
ACCESS_TOKEN="${PATHAO_ACCESS_TOKEN:-YOUR_ACCESS_TOKEN_HERE}"
STORE_ID="${PATHAO_STORE_ID:-4821}"

curl -s --location "${BASE_URL}/aladdin/api/v1/orders" \
  --header "Content-Type: application/json" \
  --header "Authorization: Bearer ${ACCESS_TOKEN}" \
  --data '{
    "store_id": '"$STORE_ID"',
    "merchant_order_id": "ORD-'$(date +%s)'",
    "recipient_name": "Tariqul Islam",
    "recipient_phone": "01711223344",
    "recipient_secondary_phone": "01511223344",
    "recipient_address": "House 14, Road 7, Sector 3, Uttara, Dhaka-1230",
    "recipient_city": 1,
    "recipient_zone": 1070,
    "recipient_area": 37,
    "delivery_type": 48,
    "item_type": 2,
    "special_instruction": "Call before arrival",
    "item_quantity": 1,
    "item_weight": 0.5,
    "item_description": "Mens Polo Shirt",
    "amount_to_collect": 1200
  }' | jq .
