#!/usr/bin/env bash
# ==============================================================================
# Paperfly Courier API - Cancel Order
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PAPERFLY_BASE_URL:-https://api.paperfly.com.bd}"
USERNAME="${PAPERFLY_USERNAME:-sellerone}"
PASSWORD="${PAPERFLY_PASSWORD:-YOUR_PASSWORD}"
PAPERFLY_KEY="${PAPERFLY_KEY:-Paperfly_~La?Rj73FcLm}"
ORDER_ID="${1:-Test_01610}"

curl -s --location "${BASE_URL}/api/v1/cancel-order" \
  --header "Content-Type: application/json" \
  --header "paperflykey: ${PAPERFLY_KEY}" \
  --user "${USERNAME}:${PASSWORD}" \
  --data '{
    "order_id": "'"$ORDER_ID"'"
  }' | jq .
