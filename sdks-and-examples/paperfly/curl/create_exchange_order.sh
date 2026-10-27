#!/usr/bin/env bash
# ==============================================================================
# Paperfly Courier API - Create Exchange Order
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PAPERFLY_BASE_URL:-https://api.paperfly.com.bd}"
USERNAME="${PAPERFLY_USERNAME:-sellerone}"
PASSWORD="${PAPERFLY_PASSWORD:-YOUR_PASSWORD}"
PAPERFLY_KEY="${PAPERFLY_KEY:-Paperfly_~La?Rj73FcLm}"

curl -s --location "${BASE_URL}/merchant/api/service/new_order_v2.php" \
  --header "Content-Type: application/json" \
  --header "paperflykey: ${PAPERFLY_KEY}" \
  --user "${USERNAME}:${PASSWORD}" \
  --data '{
    "merchantOrderReference": "EXC-'$(date +%s)'",
    "storeName": "Main Outlet",
    "productBrief": "Men Leather Shoes - Size 43",
    "packagePrice": "0",
    "max_weight": "1.0",
    "customerName": "Liton Ovi",
    "customerAddress": "Banani, Dhaka",
    "customerPhone": "01610202717",
    "orderType": "Exchange",
    "exchangeDescription": "Return Damaged Size 42",
    "exchangePrice": "100",
    "exchangeWeight": "1.0"
  }' | jq .
