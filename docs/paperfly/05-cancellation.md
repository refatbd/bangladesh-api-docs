# 05. Order Cancellation API

Merchants can cancel an active parcel booking prior to rider pickup using Paperfly's dedicated cancellation endpoint.

---

## Cancel Order Endpoint

- **Method:** `POST`
- **Endpoint:** `https://api.paperfly.com.bd/api/v1/cancel-order`

### Request Headers

```http
Authorization: Basic <base64(username:password)>
paperflykey: Paperfly_~La?Rj73FcLm
Content-Type: application/json
Accept: application/json
```

---

## Request Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `order_id` | `string` | **Yes** | Your original `merchantOrderReference` that was supplied during order creation. |

### Sample Request (cURL)

```bash
curl --location 'https://api.paperfly.com.bd/api/v1/cancel-order' \
  --header 'Content-Type: application/json' \
  --header 'paperflykey: Paperfly_~La?Rj73FcLm' \
  --header 'Authorization: Basic YOUR_BASE64_AUTH' \
  --data '{
    "order_id": "INV_2026_9041"
  }'
```

---

## Sample Response (`200 OK`)

```json
{
  "success": {
    "message": "succesfully canceled",
    "response_code": 200
  }
}
```

---

## ⚠️ Cancellation Rules & Operational Constraints

1. **Pre-Pickup Only**: A cancellation is only accepted if the parcel has not yet been physically collected by the pickup rider (`Pick` status is not `"Y"`).
2. **Post-Pickup Scenarios**: If the rider has already scanned and picked up the consignment, calling this endpoint will fail. In that scenario, the parcel must proceed through Paperfly's return workflow (**Smart Action / Return Request** from the merchant panel).
3. **Idempotency**: Repeated cancellation requests for already cancelled orders will return a confirmation without causing system exceptions.
