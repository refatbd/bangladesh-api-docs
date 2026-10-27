# 04. Step 2: Checkout Complete & Redirection

Once the `paymentReferenceId` is obtained and verified, your backend executes the order completion request to generate the secure, hosted Nagad payment gateway URL.

---

## 1. Endpoint Specification

- **Method**: `POST`
- **URL**:
  - **Sandbox**: `http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/check-out/complete/{paymentReferenceId}`
  - **Production**: `https://api.mynagad.com/api/dfs/check-out/complete/{paymentReferenceId}`
- **Headers**:
  ```http
  Content-Type: application/json
  X-KM-Api-Version: v-0.2.0
  X-KM-IP-V4: YOUR_SERVER_PUBLIC_IP
  X-KM-Client-Type: PC_WEB
  ```

---

## 2. Request Construction

### A. Inner `sensitiveDataOrder` Object (Plain JSON)

```json
{
  "merchantId": "6800000025",
  "orderId": "INV-2026-001",
  "currencyCode": "050",
  "amount": "1500.00",
  "challenge": "4a7b9c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b"
}
```

- `currencyCode`: Strictly **`050`** (the 3-digit ISO 4217 numeric code for Bangladeshi Taka, BDT). Do not pass `"BDT"`.
- `amount`: Transaction amount formatted as a decimal string or float (e.g. `"1500.00"`).
- `challenge`: The same challenge string echoed from Step 1.

### B. Cryptographic Operations
1. `sensitiveDataOrder` is encrypted using the **Nagad PG Public Key** (`PKCS#1 v1.5`).
2. `sensitiveDataOrder` is signed using your **Merchant Private Key** (`SHA-256 with RSA`).

### C. Final HTTP Request Body

```json
{
  "sensitiveData": "RW5jcnlwdGVkT3JkZXJQYXlsb2FkVXNpbmdQZ1B1YmxpY0tleS4uLg==",
  "signature": "U2lnbmF0dXJlT2ZPcmRlclBheWxvYWRVc2luZ1ByaXZhdGVLZXkuLi4=",
  "merchantCallbackURL": "https://merchant.example.com/payment/nagad/callback"
}
```

> [!NOTE]
> `merchantCallbackURL` must be whitelisted with Nagad during merchant account onboarding. Mismatched domain names or protocol schemes (HTTP vs HTTPS) will cause Nagad to throw an invalid callback error.

---

## 3. Gateway Response & Redirection

### Response Payload (`200 OK`)

```json
{
  "status": "Success",
  "callBackUrl": "http://sandbox.mynagad.com:10060/check-out/MDYyODAwNTcyNTYxNi42ODMwMDIwMDcxMDQyMjUuOU5PTEFVNkVaWkdUWVRBLmJiZGMyNTE3MTVmZTNiNjIzN2Zk"
}
```

### Performing Browser Redirection
Redirect the customer's browser window to the returned `callBackUrl`:

- **PHP**:
  ```php
  header('Location: ' . $response['callBackUrl']);
  exit;
  ```
- **Node.js (Express)**:
  ```javascript
  res.redirect(response.callBackUrl);
  ```
- **Next.js**:
  ```javascript
  return NextResponse.redirect(response.callBackUrl);
  ```

The customer will now see the official Nagad hosted gateway where they enter their 11-digit mobile number, receive an SMS OTP, and enter their 4-digit PIN.
