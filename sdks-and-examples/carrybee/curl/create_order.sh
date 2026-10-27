#!/usr/bin/env bash
# Carrybee Courier API v2 - Create Order
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${CARRYBEE_BASE_URL:-https://sandbox.carrybee.com}"
CLIENT_ID="${CARRYBEE_CLIENT_ID:-YOUR_CLIENT_ID}"
CLIENT_SECRET="${CARRYBEE_CLIENT_SECRET:-YOUR_CLIENT_SECRET}"
CLIENT_CONTEXT="${CARRYBEE_CLIENT_CONTEXT:-YOUR_CLIENT_CONTEXT}"

curl -s -X POST "${BASE_URL}/api/v2/orders" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "store_id": 101,
    "merchant_order_id": "INV-2026-CB-001",
    "recipient_name": "Tanvir Ahmed",
    "recipient_phone": "01811223344",
    "recipient_address": "House 12, Road 4, Sector 7, Uttara, Dhaka",
    "recipient_city": 1,
    "recipient_zone": 5,
    "delivery_type": 1,
    "product_type": 1,
    "item_weight": 1200,
    "item_description": "Smart Casual Denim Shirt - Size L",
    "item_quantity": 1,
    "collectable_amount": 1850,
    "special_instruction": "Call recipient before dispatching"
  }' | jq .
