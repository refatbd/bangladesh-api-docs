# 📦 Steadfast Courier API Documentation (v1)

> Comprehensive, production-verified developer guide for **Steadfast Courier Limited** (Bangladesh).  
> Maintained & documented by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 🧭 Navigation & Chapters

1. **[01. Getting Started & Authentication](01-getting-started.md)**
   - Base Gateway URL (`https://portal.packzy.com/api/v1`)
   - Mandatory HTTP Headers (`Api-Key`, `Secret-Key`)
   - Rate Limits (1,000 req/min general, 6,000 for booking)
   - Character Stripping `{ } ; < > $` & Value Truncation Limits
   - Health Check Ping (`GET /ping`)

2. **[02. Booking Parcels](02-booking-parcels.md)**
   - Single Parcel Creation (`POST /create_order`)
   - Bulk Booking up to 500 parcels (`POST /create_order/bulk-order`)
   - Extended Bulk Booking with field validation (`POST /create_order/bulk-order/extended`)

3. **[03. Checking Parcel Status & Tracking](03-tracking-and-status.md)**
   - Query by Consignment ID (`GET /status_by_cid/{consignment_id}`)
   - Query with Return Progress (`GET /status_with_return_status_by_cid/{consignment_id}`)
   - Query by Merchant Invoice (`GET /status_by_invoice/{invoice}`)
   - Query by Courier Tracking Code (`GET /status_by_trackingcode/{tracking_code}`)
   - Full Event History Timeline (`GET /trackings_by_invoice/{invoice}`)

4. **[04. Rider Pickups & Return Requests](04-pickups-and-returns.md)**
   - Request Rider Collection (`POST /create_pickup_request`)
   - Request Parcel Return (`POST /create_return_request`)
   - View Return Requests List (`GET /get_return_requests`)
   - View Single Return Request (`GET /get_return_request/{id}`)

5. **[05. Wallet Balance & COD Settlements](05-balance-and-payments.md)**
   - Check Current Merchant Balance (`GET /get_balance`)
   - View Payout History (`GET /payments`)
   - View Detailed Payment Settlement (`GET /payments/{payment_id}`)

6. **[06. Delivery Risk Fraud Check & Thanas](06-fraud-check-and-thanas.md)**
   - Customer Delivery Risk Score (`GET /fraud_check/score/{phone}`)
   - Coverage Directory of Police Stations / Thanas (`GET /police_stations`)

7. **[07. Status Codes & Error Reference](07-status-code-reference.md)**
   - Complete Delivery Status State Machine (`pending`, `in_review`, `delivered`, `cancelled`, etc.)
   - Return Statuses (`partial_delivered_return_processing`, etc.)
   - HTTP Response Codes & Error Handling (200, 400, 401, 403, 422, 429, 500)

8. **[08. Webhooks](08-webhooks.md)**
   - Configuring Real-time Webhooks
   - Handling Movement, Return, and Payout Events without Polling

---

## ⚡ Quick Start: 30-Second Example (cURL)

```bash
# 1. Health Check (No API Key Required)
curl -X GET https://portal.packzy.com/api/v1/ping

# 2. Check Your Wallet Balance
curl -X GET https://portal.packzy.com/api/v1/get_balance \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Secret-Key: YOUR_SECRET_KEY" \
  -H "Accept: application/json"

# 3. Book a Parcel
curl -X POST https://portal.packzy.com/api/v1/create_order \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Secret-Key: YOUR_SECRET_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice": "INV-2026-001",
    "recipient_name": "Rahim Ahmed",
    "recipient_phone": "01712345678",
    "recipient_address": "House 12, Road 5, Dhanmondi, Dhaka",
    "cod_amount": 1250,
    "note": "Handle with care"
  }'
```

---

## 🛠️ SDKs & Ready-to-Use Clients

Check the [sdks-and-examples/steadfast](../../sdks-and-examples/steadfast/) folder for ready-to-use clients:
- **PHP 8.x / Laravel Client**: [SteadfastClient.php](../../sdks-and-examples/steadfast/php/SteadfastClient.php)
- **Node.js (ES6 / async-await)**: [steadfast.js](../../sdks-and-examples/steadfast/nodejs/steadfast.js)
- **Python 3 Requests Client**: [steadfast.py](../../sdks-and-examples/steadfast/python/steadfast.py)
- **Shell Scripts**: [sdks-and-examples/steadfast/curl/](../../sdks-and-examples/steadfast/curl/)
- **Postman Collection**: [Steadfast_Courier_API_v1.postman_collection.json](../../postman/Steadfast_Courier_API_v1.postman_collection.json)
- **OpenAPI 3.0 Spec**: [steadfast-openapi.yaml](../../openapi/steadfast-openapi.yaml)
