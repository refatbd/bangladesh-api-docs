#!/usr/bin/env bash
# ==============================================================================
# Pathao Courier API - Issue Access Token
# Author: Refat Rahman (https://refat.ovh | https://github.com/refatbd)
# ==============================================================================

BASE_URL="${PATHAO_BASE_URL:-https://courier-api-sandbox.pathao.com}"
CLIENT_ID="${PATHAO_CLIENT_ID:-7N1aMJQbWm}"
CLIENT_SECRET="${PATHAO_CLIENT_SECRET:-wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39}"
USERNAME="${PATHAO_USERNAME:-test@pathao.com}"
PASSWORD="${PATHAO_PASSWORD:-lovePathao}"

curl -s --location "${BASE_URL}/aladdin/api/v1/issue-token" \
  --header "Content-Type: application/json" \
  --data-raw '{
    "client_id": "'"$CLIENT_ID"'",
    "client_secret": "'"$CLIENT_SECRET"'",
    "grant_type": "password",
    "username": "'"$USERNAME"'",
    "password": "'"$PASSWORD"'"
  }' | jq .
