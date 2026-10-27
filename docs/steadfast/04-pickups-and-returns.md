# 04. Rider Pickups & Return Requests

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 🛵 1. Create Pickup Request (`POST /create_pickup_request`)

Ask for a Steadfast rider to collect parcels from one of your registered pickup addresses.

### Endpoint
```http
POST https://portal.packzy.com/api/v1/create_pickup_request
Content-Type: application/json
Api-Key: YOUR_API_KEY
Secret-Key: YOUR_SECRET_KEY
```

### Request Payload
```json
{
  "pickup_address": "House 10, Road 4, Sector 3, Uttara, Dhaka",
  "phone": "01700000000",
  "total_parcels": 25,
  "note": "Ready for pickup between 2 PM and 5 PM"
}
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "message": "Pickup request placed successfully",
  "pickup_id": 48210
}
```

---

## ↩️ 2. Create Return Request (`POST /create_return_request`)

Ask for a parcel to be aborted and returned to you before delivery is completed (e.g., customer cancellation or fraud detection).

### Endpoint
```http
POST https://portal.packzy.com/api/v1/create_return_request
Content-Type: application/json
```

### Request Payload
```json
{
  "consignment_id": 9845210,
  "reason": "Customer called and cancelled order before dispatch"
}
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "message": "Return request created successfully",
  "return_id": 11520
}
```

---

## 📋 3. Get Return Requests List (`GET /get_return_requests`)

Fetches your submitted return requests, ordered newest first, **10 per page**.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/get_return_requests?page=1
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "data": [
    {
      "id": 11520,
      "consignment_id": 9845210,
      "invoice": "ORD-10231",
      "reason": "Customer called and cancelled order before dispatch",
      "status": "approved",
      "created_at": "2026-10-27 10:30:00"
    }
  ]
}
```

---

## 🔎 4. Get Single Return Request (`GET /get_return_request/{id}`)

Inspect one specific return request along with the parcel details.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/get_return_request/11520
```
