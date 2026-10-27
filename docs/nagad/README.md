# 💳 Nagad Direct Online Payment Gateway (API v3.3)

Official technical integration guide, cryptography specifications, and production client libraries for **Nagad Direct Online Payment Gateway** (developed by Kona Software Lab Ltd. for Third Wave Technologies Ltd. / Postal Department of Bangladesh).

Maintained with Silicon Valley engineering rigor by **Refat Rahman** ([refat.ovh](https://refat.ovh) | [@refatbd](https://github.com/refatbd)).

---

## 1. Architectural Overview & Highlights

Nagad is Bangladesh's state-backed Mobile Financial Service (MFS) network, processing millions of daily transactions. Unlike conventional REST APIs that rely solely on Bearer JWTs or Basic Auth, Nagad enforces an **enterprise-grade asymmetric cryptographic handshake**:

- **Dual-Key RSA 2048-bit Cryptography**: Every merchant payload is signed with the Merchant's Private Key and encrypted using Nagad's Payment Gateway Public Key (`PKCS#1 v1.5`).
- **4-Stage Secure Checkout Flow**:
  1. `POST /check-out/initialize/{merchantId}/{orderId}`: Exchanges cryptographic challenge and returns an encrypted `paymentReferenceId`.
  2. `POST /check-out/complete/{paymentReferenceId}`: Submits payment amount, currency code (`050` BDT), and callback URL, returning a hosted Nagad checkout URL.
  3. **Browser Redirection**: Customer enters Nagad Wallet Number, OTP, and PIN on Nagad's secure PCI-DSS compliant checkout gateway.
  4. `GET /verify/payment/{paymentRefId}`: Merchant server independently verifies the transaction with Nagad's core banking engine to eliminate callback spoofing.
- **Strict Server IP Whitelisting**: Outbound requests must originate from IP addresses explicitly approved and whitelisted by Nagad security operations.

```mermaid
sequenceDiagram
    autonumber
    participant Customer as Customer Browser
    participant Merchant as Merchant Backend
    participant NagadPG as Nagad Gateway API
    participant NagadUI as Nagad Hosted Checkout

    Customer->>Merchant: 1. Checkout (Order BDT 100)
    Note over Merchant: Generate Challenge<br/>Encrypt with Nagad Public Key<br/>Sign with Merchant Private Key
    Merchant->>NagadPG: 2. POST /check-out/initialize/{merchantId}/{orderId}
    NagadPG-->>Merchant: 3. Encrypted paymentReferenceId + Challenge Echo
    Note over Merchant: Decrypt with Merchant Private Key<br/>Encrypt Order Amount (Currency 050)
    Merchant->>NagadPG: 4. POST /check-out/complete/{paymentReferenceId}
    NagadPG-->>Merchant: 5. Hosted callBackUrl (http://sandbox.mynagad.com:10060/...)
    Merchant-->>Customer: 6. Redirect to Nagad Hosted Gateway
    Customer->>NagadUI: 7. Enter Wallet Phone, OTP & PIN
    NagadUI-->>Customer: 8. Payment Processed, Redirect to Merchant Callback
    Customer->>Merchant: 9. GET /callback?payment_ref_id=...&status=Success
    Merchant->>NagadPG: 10. GET /verify/payment/{paymentRefId}
    NagadPG-->>Merchant: 11. Transaction Verified (amount: 100, status: Success)
    Merchant-->>Customer: 12. Display Order Confirmation
```

---

## 2. Environments & Base URLs

| Environment | Base API URL | Hosted Checkout UI Port | Purpose |
| :--- | :--- | :--- | :--- |
| **Sandbox / Staging** | `http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/` | `10060` | Functional testing, end-to-end sandbox wallet flows |
| **Production / Live** | `https://api.mynagad.com/api/dfs/` | `443` (HTTPS) | Live commercial customer payments across Bangladesh |

---

## 3. Mandatory HTTP Headers

Every request to the Nagad API Gateway requires standard and specialized headers:

```http
Content-Type: application/json
X-KM-Api-Version: v-0.2.0
X-KM-IP-V4: YOUR_SERVER_PUBLIC_IP
X-KM-Client-Type: PC_WEB
```

- `X-KM-Api-Version`: Always set to `v-0.2.0`.
- `X-KM-IP-V4`: Your backend server's IPv4 address. Must match the IP submitted during merchant onboarding.
- `X-KM-Client-Type`: `PC_WEB` for desktop/mobile browsers, or `MOBILE_APP` for native smartphone apps.

---

## 4. Cryptographic Credentials Model

Integrating with Nagad requires four specific assets:

1. **`merchantId`**: 10 to 15 digit unique merchant account number (e.g. `683002007104225`).
2. **`merchantPrivateKey`**: 2048-bit RSA Private Key in PEM format (`-----BEGIN RSA PRIVATE KEY-----`). Kept strictly secret on your server.
3. **`pgPublicKey`**: 2048-bit RSA Public Key provided by Nagad (`-----BEGIN PUBLIC KEY-----`).
4. **Timezone**: Must be synchronized with `Asia/Dhaka` (`YYYYMMDDHHmmss` timestamp format).

---

## 5. Chapter Navigation

1. [**01. Getting Started & Merchant Onboarding**](01-getting-started.md)
2. [**02. Cryptography, Signatures & Key Generation**](02-cryptography-and-signatures.md)
3. [**03. Step 1: Checkout Initialization**](03-checkout-initialization.md)
4. [**04. Step 2: Checkout Complete & Redirection**](04-checkout-complete-and-redirect.md)
5. [**05. Step 3 & 4: Callback Handling & Verification**](05-callback-and-verification.md)
6. [**06. Error Codes, Status Mapping & Troubleshooting**](06-error-codes-and-status.md)
