#!/usr/bin/env bash
# Nagad Payment Gateway v3.3 - Verify Payment by Payment Reference ID
# Author: Refat Rahman (https://refat.ovh | @refatbd)

BASE_URL="${NAGAD_BASE_URL:-http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs}"
CLIENT_IP="${NAGAD_CLIENT_IP:-103.145.118.24}"
PAYMENT_REF_ID="${1:-MDYyODAwNTcyNTYxNi42ODMwMDIwMDcxMDQyMjUuOU5PTEFVNkVaWkdUWVRBLmJiZGMyNTE3MTVmZTNiNjIzN2Zk}"

curl -s -X GET "${BASE_URL}/verify/payment/${PAYMENT_REF_ID}" \
  -H "X-KM-Api-Version: v-0.2.0" \
  -H "X-KM-IP-V4: ${CLIENT_IP}" \
  -H "X-KM-Client-Type: PC_WEB" \
  -H "Accept: application/json" | jq .
