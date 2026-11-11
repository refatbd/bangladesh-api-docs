#!/usr/bin/env bash
# ==============================================================================
# RedX Courier API - Create Parcel
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${REDX_BASE_URL:-https://sandbox.redx.com.bd/v1.0.0-beta}"
JWT_TOKEN="${REDX_JWT_TOKEN:-YOUR_JWT_TOKEN_HERE}"

curl -s --location "${BASE_URL}/parcel" \
  --header "API-ACCESS-TOKEN: Bearer ${JWT_TOKEN}" \
  --header "Content-Type: application/json" \
  --data '{
      "customer_name": "Rahim Ahmed",
      "customer_phone": "01711223344",
      "delivery_area": "Mirpur DOHS",
      "delivery_area_id": 12,
      "customer_address": "House 45, Road 7, Avenue 3, Mirpur DOHS, Dhaka",
      "merchant_invoice_id": "INV-'$(date +%s)'",
      "cash_collection_amount": "1250",
      "paid_return_charge": 0,
      "parcel_weight": 500,
      "instruction": "Fragile items. Handle with care.",
      "value": 1250,
      "is_closed_box": true,
      "pickup_store_id": 1,
      "parcel_details_json": [
          {
              "name": "Men Casual Shirt",
              "category": "Fashion",
              "value": 1250
          }
      ]
  }' | jq .
