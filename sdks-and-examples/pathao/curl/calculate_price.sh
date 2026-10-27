#!/usr/bin/env bash
# ==============================================================================
# Pathao Courier API - Price Calculation Plan
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PATHAO_BASE_URL:-https://courier-api-sandbox.pathao.com}"
ACCESS_TOKEN="${PATHAO_ACCESS_TOKEN:-YOUR_ACCESS_TOKEN_HERE}"
STORE_ID="${PATHAO_STORE_ID:-4821}"

curl -s --location "${BASE_URL}/aladdin/api/v1/merchant/price-plan" \
  --header "Content-Type: application/json; charset=UTF-8" \
  --header "Authorization: Bearer ${ACCESS_TOKEN}" \
  --data '{
    "store_id": '"$STORE_ID"',
    "item_type": 2,
    "delivery_type": 48,
    "item_weight": 0.5,
    "recipient_city": 1,
    "recipient_zone": 1070
  }' | jq .
