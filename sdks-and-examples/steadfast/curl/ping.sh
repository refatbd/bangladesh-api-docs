#!/usr/bin/env bash
# Steadfast Courier Gateway Ping Check
# Maintained by Refat Rahman (https://github.com/refatbd)

curl -s -X GET "https://portal.packzy.com/api/v1/ping" | jq .
