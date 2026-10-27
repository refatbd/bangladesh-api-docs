#!/usr/bin/env bash
# Carrybee Courier API v2 - Track Order by Consignment ID
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${CARRYBEE_BASE_URL:-https://sandbox.carrybee.com}"
CLIENT_ID="${CARRYBEE_CLIENT_ID:-YOUR_CLIENT_ID}"
CLIENT_SECRET="${CARRYBEE_CLIENT_SECRET:-YOUR_CLIENT_SECRET}"
CLIENT_CONTEXT="${CARRYBEE_CLIENT_CONTEXT:-YOUR_CLIENT_CONTEXT}"

CONSIGNMENT_ID="${1:-CB-982341}"

curl -s -X GET "${BASE_URL}/api/v2/orders/track/${CONSIGNMENT_ID}" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Accept: application/json" | jq .
