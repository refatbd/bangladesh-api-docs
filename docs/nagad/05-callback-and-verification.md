# 05. Step 3 & 4: Callback Handling & Payment Verification

After the customer confirms payment on Nagad's hosted checkout screen, their browser is returned to your `merchantCallbackURL`. Because browser redirects can be intercepted or spoofed, you **must never mark an order as paid without server-to-server verification**.

---

## 1. Handling the Inbound Callback

When Nagad redirects the customer's browser, it appends status parameters as query strings to your `merchantCallbackURL`:

```http
GET https://merchant.example.com/payment/nagad/callback?
  merchant=683002007104225
  &order_id=INV-2026-001
  &payment_ref_id=MDYyODAwNTcyNTYxNi42ODMwMDIwMDcxMDQyMjUuOU5PTEFVNkVaWkdUWVRBLmJiZGMyNTE3MTVmZTNiNjIzN2Zk
  &status=Success
  &status_code=00_0000_000
  &message=Successful%20Transaction
  &payment_dt=20261027133508
  &issuer_payment_ref=MTEyMzIzNDg1NzUwOS42ODMwMDIwMDcxMDQyMjUu
```

### Callback Parameter Glossary
- `payment_ref_id`: The unique transaction identifier used to verify payment.
- `status`: Preliminary status (`Success`, `Failed`, or `Aborted`).
- `status_code`: Internal status code (e.g. `00_0000_000` for successful auth).
- `order_id`: Your original merchant order ID.

> [!CAUTION]
> **Zero-Trust Security**: Anyone can manually visit `https://merchant.example.com/payment/nagad/callback?status=Success`. Always take the `payment_ref_id` and query Nagad's verification API directly from your backend.

---

## 2. Server-to-Server Payment Verification (`GET /verify/payment/{paymentRefId}`)

Query Nagad's core banking ledger to obtain the authoritative, immutable state of the transaction.

### Endpoint Specification
- **Method**: `GET`
- **URL**:
  - **Sandbox**: `http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/verify/payment/{paymentRefId}`
  - **Production**: `https://api.mynagad.com/api/dfs/verify/payment/{paymentRefId}`
- **Headers**:
  ```http
  X-KM-Api-Version: v-0.2.0
  X-KM-IP-V4: YOUR_SERVER_PUBLIC_IP
  X-KM-Client-Type: PC_WEB
  ```

### cURL
```bash
curl -X GET "https://api.mynagad.com/api/dfs/verify/payment/MDYyODAwNTcyNTYxNi42ODMwMDIwMDcxMDQyMjUuOU5PTEFVNkVaWkdUWVRBLmJiZGMyNTE3MTVmZTNiNjIzN2Zk" \
  -H "X-KM-Api-Version: v-0.2.0" \
  -H "X-KM-IP-V4: 103.145.118.24" \
  -H "X-KM-Client-Type: PC_WEB"
```

---

## 3. Verification Response Schema

### Successful Transaction (`HTTP 200 OK`)

```json
{
  "merchantId": "683002007104225",
  "orderId": "INV-2026-001",
  "paymentRefId": "MDYyODAwNTcyNTYxNi42ODMwMDIwMDcxMDQyMjUuOU5PTEFVNkVaWkdUWVRBLmJiZGMyNTE3MTVmZTNiNjIzN2Zk",
  "amount": "1500.00",
  "clientMobileNo": "017****2300",
  "merchantMobileNo": "01700000000",
  "orderDateTime": "2026-10-27 13:30:00.0",
  "issuerPaymentDateTime": "2026-10-27 13:35:08.0",
  "issuerPaymentRefNo": "000XXXW",
  "additionalMerchantInfo": null,
  "status": "Success",
  "statusCode": "000",
  "cancelIssuerDateTime": null,
  "cancelIssuerRefNo": null
}
```

---

## 4. Production Reconciliation Checklist

Before crediting user balance or fulfilling the order, your backend code must assert all 5 criteria:

1. **Transaction Status**: `response.status === "Success"`
2. **Status Code**: `response.statusCode === "000"`
3. **Order ID Integrity**: `response.orderId === databaseOrder.order_id`
4. **Amount Matching**: `parseFloat(response.amount) === parseFloat(databaseOrder.total_amount)`
5. **Idempotency Guard**: Ensure the order has not already been marked as `Completed` in your database to prevent duplicate fulfillment.
