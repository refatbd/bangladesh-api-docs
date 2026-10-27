#!/usr/bin/env bash
# Carrybee Courier API v2 - Create Exchange Order
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${CARRYBEE_BASE_URL:-https://sandbox.carrybee.com}"
CLIENT_ID="${CARRYBEE_CLIENT_ID:-YOUR_CLIENT_ID}"
CLIENT_SECRET="${CARRYBEE_CLIENT_SECRET:-YOUR_CLIENT_SECRET}"
CLIENT_CONTEXT="${CARRYBEE_CLIENT_CONTEXT:-YOUR_CLIENT_CONTEXT}"

ORIGINAL_CONSIGNMENT_ID="${1:-CB-982341}"

curl -s -X POST "${BASE_URL}/api/v2/orders/${ORIGINAL_CONSIGNMENT_ID}/exchange" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "store_id": 101,
    "replacement_item_description": "Size XL Blue Denim Shirt",
    "item_weight": 1200,
    "collectable_amount": 100,
    "exchange_reason": "Customer requested size change"
  }' | jq .
