#!/usr/bin/env bash
# Nagad Payment Gateway v3.3 - Generate RSA 2048-bit Key Pair
# Author: Refat Rahman (https://refat.ovh | @refatbd)

OUTPUT_DIR="${1:-./nagad_keys}"
mkdir -p "${OUTPUT_DIR}"

echo "Generating 2048-bit RSA Private Key..."
openssl genrsa -out "${OUTPUT_DIR}/merchant_private_key.pem" 2048

echo "Exporting corresponding RSA Public Key..."
openssl rsa -in "${OUTPUT_DIR}/merchant_private_key.pem" -pubout -out "${OUTPUT_DIR}/merchant_public_key.pem"

echo "=== RSA Key Pair Generated Successfully ==="
echo "Private Key (Keep Secret): ${OUTPUT_DIR}/merchant_private_key.pem"
echo "Public Key (Send to Nagad): ${OUTPUT_DIR}/merchant_public_key.pem"
