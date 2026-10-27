#!/usr/bin/env bash
# Carrybee Courier API v2 - Book Reverse Pickup
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${CARRYBEE_BASE_URL:-https://sandbox.carrybee.com}"
CLIENT_ID="${CARRYBEE_CLIENT_ID:-YOUR_CLIENT_ID}"
CLIENT_SECRET="${CARRYBEE_CLIENT_SECRET:-YOUR_CLIENT_SECRET}"
CLIENT_CONTEXT="${CARRYBEE_CLIENT_CONTEXT:-YOUR_CLIENT_CONTEXT}"

curl -s -X POST "${BASE_URL}/api/v2/orders/reverse-pickup" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "store_id": 101,
    "customer_name": "Rafiqul Islam",
    "customer_phone": "01722334455",
    "customer_address": "House 45, Road 11, Banani, Dhaka",
    "customer_city": 1,
    "customer_zone": 8,
    "item_weight": 850,
    "item_description": "Size M T-Shirt (Wrong size return)",
    "reason": "Customer needs size replacement"
  }' | jq .
