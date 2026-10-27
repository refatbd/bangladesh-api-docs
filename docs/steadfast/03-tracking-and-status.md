# 03. Checking Status & Tracking

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## ⏱️ The 60-Second Caching Rule

> [!WARNING]
> Status queries are cached by Steadfast for **60 seconds**. Querying the API more frequently than once every 60 seconds will yield the same cached response and burn your API rate limit. If you need real-time push events, configure a [Webhook](08-webhooks.md).

---

## 🔍 1. Status by Consignment ID (`GET /status_by_cid/{consignment_id}`)

Queries current delivery status using the unique `consignment_id` returned during booking.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/status_by_cid/{consignment_id}
Api-Key: YOUR_API_KEY
Secret-Key: YOUR_SECRET_KEY
```

### cURL
```bash
curl -X GET https://portal.packzy.com/api/v1/status_by_cid/9845210 \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Secret-Key: YOUR_SECRET_KEY" \
  -H "Accept: application/json"
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "delivery_status": "in_transit"
}
```

---

## 🔄 2. Status with Return Progress (`GET /status_with_return_status_by_cid/{consignment_id}`)

Similar to `status_by_cid`, but if a parcel was rejected, partially delivered, or returned, it reveals the exact return stage.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/status_with_return_status_by_cid/{consignment_id}
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "delivery_status": "cancelled",
  "return_status": "cancelled_return_rider_assigned"
}
```

---

## 🧾 3. Status by Merchant Invoice (`GET /status_by_invoice/{invoice}`)

Retrieve parcel status using your store's own invoice number without looking up the internal Steadfast ID.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/status_by_invoice/{invoice}
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "delivery_status": "delivered"
}
```

---

## 🏷️ 4. Status by Tracking Code (`GET /status_by_trackingcode/{tracking_code}`)

Query status using the customer-facing tracking number printed on the shipping label (e.g., `ST-9845210`).

### Endpoint
```http
GET https://portal.packzy.com/api/v1/status_by_trackingcode/{tracking_code}
```

---

## 📜 5. Full Tracking Timeline (`GET /trackings_by_invoice/{invoice}`)

Returns the complete journey and timeline events of a parcel from booking to delivery or return.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/trackings_by_invoice/{invoice}
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "tracking_data": [
    {
      "step": 1,
      "status": "in_review",
      "message": "Parcel booked and placed in review.",
      "timestamp": "2026-10-25 14:20:00"
    },
    {
      "step": 2,
      "status": "pending",
      "message": "Approved by Steadfast hub. Rider assigned for collection.",
      "timestamp": "2026-10-25 16:00:00"
    },
    {
      "step": 3,
      "status": "in_transit",
      "message": "Parcel received at Central Hub and dispatched to destination branch.",
      "timestamp": "2026-10-26 09:30:00"
    },
    {
      "step": 4,
      "status": "delivered",
      "message": "Parcel delivered to customer and COD collected.",
      "timestamp": "2026-10-27 11:15:00"
    }
  ]
}
```
