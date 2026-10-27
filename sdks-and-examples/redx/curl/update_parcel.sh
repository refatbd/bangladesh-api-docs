#!/usr/bin/env bash
# ==============================================================================
# RedX Courier API - Update Parcel (Cancel or Update Property)
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${REDX_BASE_URL:-https://sandbox.redx.com.bd/v1.0.0-beta}"
JWT_TOKEN="${REDX_JWT_TOKEN:-YOUR_JWT_TOKEN_HERE}"
TRACKING_ID="${1:-21A427TU4BN3R}"
REASON="${2:-Customer requested cancellation}"

curl -s --location --request PATCH "${BASE_URL}/parcels" \
  --header "API-ACCESS-TOKEN: Bearer ${JWT_TOKEN}" \
  --header "Content-Type: application/json" \
  --data '{
      "entity_type": "parcel-tracking-id",
      "entity_id": "'"$TRACKING_ID"'",
      "update_details": {
          "property_name": "status",
          "new_value": "cancelled",
          "reason": "'"$REASON"'"
      }
  }' | jq .
