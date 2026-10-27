#!/usr/bin/env bash
# eCourier Courier API v5.5 - Fraud Alert Status Check
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${ECOURIER_BASE_URL:-https://staging.ecourier.com.bd/api}"
API_KEY="${ECOURIER_API_KEY:-YOUR_API_KEY}"
API_SECRET="${ECOURIER_API_SECRET:-YOUR_API_SECRET}"
USER_ID="${ECOURIER_USER_ID:-YOUR_USER_ID}"

PHONE_NUMBER="${1:-01811223344}"

curl -s -X POST "${BASE_URL}/fraud-status-check" \
  -H "API-KEY: ${API_KEY}" \
  -H "API-SECRET: ${API_SECRET}" \
  -H "USER-ID: ${USER_ID}" \
  -H "Content-Type: application/json" \
  -d "{
    \"number\": \"${PHONE_NUMBER}\"
  }" | jq .
