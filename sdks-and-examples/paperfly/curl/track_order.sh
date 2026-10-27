#!/usr/bin/env bash
# ==============================================================================
# Paperfly Courier API - Track Order
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PAPERFLY_BASE_URL:-https://api.paperfly.com.bd}"
USERNAME="${PAPERFLY_USERNAME:-sellerone}"
PASSWORD="${PAPERFLY_PASSWORD:-YOUR_PASSWORD}"
PAPERFLY_KEY="${PAPERFLY_KEY:-Paperfly_~La?Rj73FcLm}"
REF_NUMBER="${1:-Test_01610}"

curl -s --location "${BASE_URL}/API-Order-Tracking" \
  --header "Content-Type: application/json" \
  --header "paperflykey: ${PAPERFLY_KEY}" \
  --user "${USERNAME}:${PASSWORD}" \
  --data '{
    "ReferenceNumber": "'"$REF_NUMBER"'"
  }' | jq .
