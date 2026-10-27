#!/usr/bin/env bash
# ==============================================================================
# RedX Courier API - Track Parcel by Tracking ID
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${REDX_BASE_URL:-https://sandbox.redx.com.bd/v1.0.0-beta}"
JWT_TOKEN="${REDX_JWT_TOKEN:-YOUR_JWT_TOKEN_HERE}"
TRACKING_ID="${1:-21A427TU4BN3R}"

echo "Tracking RedX Parcel: $TRACKING_ID"

curl -s --location "${BASE_URL}/parcel/track/${TRACKING_ID}" \
  --header "API-ACCESS-TOKEN: Bearer ${JWT_TOKEN}" \
  --header "Accept: application/json" | jq .
