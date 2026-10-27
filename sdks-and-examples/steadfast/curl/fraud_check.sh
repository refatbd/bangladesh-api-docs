#!/usr/bin/env bash
# Steadfast Courier Customer Delivery Risk & Fraud Check
# Maintained by Refat Rahman (https://github.com/refatbd)

API_KEY="${STEADFAST_API_KEY:-YOUR_API_KEY}"
SECRET_KEY="${STEADFAST_SECRET_KEY:-YOUR_SECRET_KEY}"
PHONE="${1:-01712345678}"

curl -s -X GET "https://portal.packzy.com/api/v1/fraud_check/score/${PHONE}" \
  -H "Api-Key: ${API_KEY}" \
  -H "Secret-Key: ${SECRET_KEY}" \
  -H "Accept: application/json" | jq .
