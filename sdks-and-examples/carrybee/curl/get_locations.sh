#!/usr/bin/env bash
# Carrybee Courier API v2 - Get Locations & Coverage
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${CARRYBEE_BASE_URL:-https://sandbox.carrybee.com}"
CLIENT_ID="${CARRYBEE_CLIENT_ID:-YOUR_CLIENT_ID}"
CLIENT_SECRET="${CARRYBEE_CLIENT_SECRET:-YOUR_CLIENT_SECRET}"
CLIENT_CONTEXT="${CARRYBEE_CLIENT_CONTEXT:-YOUR_CLIENT_CONTEXT}"

echo "=== 1. Fetching Cities ==="
curl -s -X GET "${BASE_URL}/api/v2/cities" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Accept: application/json" | jq '.data[0:3]'

echo "=== 2. Fetching Zones for City ID 1 (Dhaka) ==="
curl -s -X GET "${BASE_URL}/api/v2/zones/1" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Accept: application/json" | jq '.data[0:3]'

echo "=== 3. Querying Area Suggestion for 'Dhanmondi' ==="
curl -s -X GET "${BASE_URL}/api/v2/area-suggestion?search=Dhanmondi" \
  -H "Client-ID: ${CLIENT_ID}" \
  -H "Client-Secret: ${CLIENT_SECRET}" \
  -H "Client-Context: ${CLIENT_CONTEXT}" \
  -H "Accept: application/json" | jq .
