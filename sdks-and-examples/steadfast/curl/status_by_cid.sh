#!/usr/bin/env bash
# Steadfast Courier Status by Consignment ID
# Maintained by Refat Rahman (https://github.com/refatbd)

API_KEY="${STEADFAST_API_KEY:-YOUR_API_KEY}"
SECRET_KEY="${STEADFAST_SECRET_KEY:-YOUR_SECRET_KEY}"
CID="${1:-9845210}"

curl -s -X GET "https://portal.packzy.com/api/v1/status_by_cid/${CID}" \
  -H "Api-Key: ${API_KEY}" \
  -H "Secret-Key: ${SECRET_KEY}" \
  -H "Accept: application/json" | jq .
