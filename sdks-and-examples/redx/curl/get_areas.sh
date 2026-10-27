#!/usr/bin/env bash
# ==============================================================================
# RedX Courier API - Get Delivery Areas (All, By Postal Code, or By District)
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${REDX_BASE_URL:-https://sandbox.redx.com.bd/v1.0.0-beta}"
JWT_TOKEN="${REDX_JWT_TOKEN:-YOUR_JWT_TOKEN_HERE}"

FILTER_TYPE="${1:-all}"
FILTER_VAL="${2:-}"

if [ "$FILTER_TYPE" == "post_code" ]; then
  ENDPOINT="/areas?post_code=${FILTER_VAL:-1206}"
elif [ "$FILTER_TYPE" == "district" ]; then
  ENDPOINT="/areas?district_name=${FILTER_VAL:-Dhaka}"
else
  ENDPOINT="/areas"
fi

curl -s --location "${BASE_URL}${ENDPOINT}" \
  --header "API-ACCESS-TOKEN: Bearer ${JWT_TOKEN}" \
  --header "Accept: application/json" | jq .
