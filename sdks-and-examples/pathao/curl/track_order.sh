#!/usr/bin/env bash
# ==============================================================================
# Pathao Courier API - Track Order Short Info
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PATHAO_BASE_URL:-https://courier-api-sandbox.pathao.com}"
ACCESS_TOKEN="${PATHAO_ACCESS_TOKEN:-YOUR_ACCESS_TOKEN_HERE}"
CONSIGNMENT_ID="${1:-PT9041A4B89}"

curl -s --location "${BASE_URL}/aladdin/api/v1/orders/${CONSIGNMENT_ID}/info" \
  --header "Authorization: Bearer ${ACCESS_TOKEN}" \
  --header "Accept: application/json" | jq .
