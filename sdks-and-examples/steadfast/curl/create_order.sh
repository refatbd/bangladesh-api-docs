#!/usr/bin/env bash
# Steadfast Courier Book Parcel Example
# Maintained by Refat Rahman (https://github.com/refatbd)

API_KEY="${STEADFAST_API_KEY:-YOUR_API_KEY}"
SECRET_KEY="${STEADFAST_SECRET_KEY:-YOUR_SECRET_KEY}"

INVOICE="ORD-$(date +%s)"

curl -s -X POST "https://portal.packzy.com/api/v1/create_order" \
  -H "Api-Key: ${API_KEY}" \
  -H "Secret-Key: ${SECRET_KEY}" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice": "'"${INVOICE}"'",
    "recipient_name": "Rahim Uddin",
    "recipient_phone": "01712345678",
    "recipient_address": "House 12, Road 4, Sector 7, Uttara, Dhaka",
    "cod_amount": 1250,
    "note": "Urgent delivery"
  }' | jq .
