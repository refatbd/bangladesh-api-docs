#!/usr/bin/env bash
# eCourier Courier API v5.5 - Track Parcel by eCourier ID or Product ID
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${ECOURIER_BASE_URL:-https://staging.ecourier.com.bd/api}"
API_KEY="${ECOURIER_API_KEY:-YOUR_API_KEY}"
API_SECRET="${ECOURIER_API_SECRET:-YOUR_API_SECRET}"
USER_ID="${ECOURIER_USER_ID:-YOUR_USER_ID}"

TRACKING_ID="${1:-ECR1030102170392}"

curl -s -X POST "${BASE_URL}/track" \
  -H "API-KEY: ${API_KEY}" \
  -H "API-SECRET: ${API_SECRET}" \
  -H "USER-ID: ${USER_ID}" \
  -H "Content-Type: application/json" \
  -d "{
    \"ecr\": \"${TRACKING_ID}\"
  }" | jq .
