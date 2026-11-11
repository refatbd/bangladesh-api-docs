# 03. Step 1: Checkout Initialization

The initialization phase establishes a cryptographic handshake between your merchant server and Nagad's gateway, generating a unique, temporary **`paymentReferenceId`** for the customer order.

---

## 1. Endpoint Specification

- **Method**: `POST`
- **URL**:
  - **Sandbox**: `http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/check-out/initialize/{merchantId}/{orderId}`
  - **Production**: `https://api.mynagad.com/api/dfs/check-out/initialize/{merchantId}/{orderId}`
- **Headers**:
  ```http
  Content-Type: application/json
  X-KM-Api-Version: v-0.2.0
  X-KM-IP-V4: 103.145.118.24
  X-KM-Client-Type: PC_WEB
  ```

---

## 2. Request Construction

### A. Inner `sensitiveData` Object (Plain JSON)

Before encryption, construct the inner sensitive parameters JSON:

```json
{
  "merchantId": "6800000025",
  "datetime": "20261027133000",
  "orderId": "INV-2026-001",
  "challenge": "4a7b9c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b"
}
```

- `merchantId`: Assigned merchant ID.
- `datetime`: Timestamp formatted strictly as `YYYYMMDDHHmmss` in Bangladesh Standard Time (`Asia/Dhaka`).
- `orderId`: Unique alphanumeric invoice or order identifier in your ecommerce database.
- `challenge`: 40-character random cryptographic nonce.

### B. Cryptographic Operations
1. `sensitiveData` string is encrypted using **Nagad PG Public Key** (`PKCS#1 v1.5`).
2. The same `sensitiveData` string is signed using **Merchant Private Key** (`SHA-256 with RSA`).

### C. Final HTTP Request Body

```json
{
  "accountNumber": "017XXXXXXXX",
  "dateTime": "20261027133000",
  "sensitiveData": "Q29uZ3JhdHVsYXRpb25zISBZb3VyIGVuY3J5cHRlZCBwYXlsb2FkIGdvZXMgaGVyZS4uLg==",
  "signature": "VmVyeVNlY3VyZURpZ2l0YWxTaWduYXR1cmVPZk1lcmNoYW50UHJpdmF0ZUtleUhlcmUuLi4="
}
```

---

## 3. Response Processing

### Gateway HTTP Response (`200 OK`)

```json
{
  "sensitiveData": "TWFzdGVyRW5jcnlwdGVkUmVzcG9uc2VGcm9tTmFnYWRQYXltZW50R2F0ZXdheUhlcmUuLi4=",
  "signature": "TmFnYWRQdWJsaWNTaWduYXR1cmVWYWxpZGF0aW9uU3RyaW5nSGVyZS4uLg=="
}
```

### Decrypting the Response
Decrypt `sensitiveData` using your **Merchant Private Key**:

```json
{
  "paymentReferenceId": "MDYyODAwNTcyNTYxNi42ODMwMDIwMDcxMDQyMjUuOU5PTEFVNkVaWkdUWVRBLmJiZGMyNTE3MTVmZTNiNjIzN2Zk",
  "challenge": "4a7b9c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b"
}
```

> [!IMPORTANT]
> **Anti-Replay Verification**: Always assert that `response.challenge === original.challenge`. If the challenges do not match, abort the checkout immediately to prevent Man-in-the-Middle (MITM) or replay attacks.

Save `paymentReferenceId` in your database session for Step 2.
