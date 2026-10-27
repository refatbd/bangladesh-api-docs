#!/usr/bin/env bash
# ==============================================================================
# RedX Courier API - Calculate Parcel Shipping Charge
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${REDX_BASE_URL:-https://sandbox.redx.com.bd/v1.0.0-beta}"
JWT_TOKEN="${REDX_JWT_TOKEN:-YOUR_JWT_TOKEN_HERE}"

DELIVERY_AREA_ID="${1:-12}"
PICKUP_AREA_ID="${2:-1}"
CASH_COLLECTION="${3:-1500}"
WEIGHT="${4:-750}"

curl -s --location "${BASE_URL}/charge/charge_calculator?delivery_area_id=${DELIVERY_AREA_ID}&pickup_area_id=${PICKUP_AREA_ID}&cash_collection_amount=${CASH_COLLECTION}&weight=${WEIGHT}" \
  --header "API-ACCESS-TOKEN: Bearer ${JWT_TOKEN}" \
  --header "Accept: application/json" | jq .
