#!/usr/bin/env bash
# Steadfast Courier Balance Check
# Maintained by Refat Rahman (https://github.com/refatbd)

API_KEY="${STEADFAST_API_KEY:-YOUR_API_KEY}"
SECRET_KEY="${STEADFAST_SECRET_KEY:-YOUR_SECRET_KEY}"

curl -s -X GET "https://portal.packzy.com/api/v1/get_balance" \
  -H "Api-Key: ${API_KEY}" \
  -H "Secret-Key: ${SECRET_KEY}" \
  -H "Accept: application/json" | jq .
