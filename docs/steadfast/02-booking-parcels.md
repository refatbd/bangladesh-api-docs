# 02. Booking Parcels

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 📦 1. Create Single Order (`POST /create_order`)

Books an individual consignment with Steadfast Courier.

### Endpoint
```http
POST https://portal.packzy.com/api/v1/create_order
Content-Type: application/json
Api-Key: YOUR_API_KEY
Secret-Key: YOUR_SECRET_KEY
```

### Request Parameters

| Parameter | Type | Required | Max Length | Description |
| :--- | :--- | :---: | :---: | :--- |
| `invoice` | String | **Yes** | 100 | Your internal order or invoice number. **Must be unique.** |
| `recipient_name` | String | **Yes** | 100 | Full name of the customer receiving the delivery. |
| `recipient_phone` | String | **Yes** | 40 | Primary contact phone number of the customer. |
| `recipient_address` | String | **Yes** | 490 | Delivery address including house, road, area, and city. |
| `cod_amount` | Number | **Yes** | - | Cash on Delivery (COD) amount in BDT. Set `0` for prepaid orders. |
| `note` | String | No | 480 | Special delivery instructions for the rider. |

> [!TIP]
> **Idempotent Retry Safety**: Steadfast refuses duplicate invoices. If your server times out during a call, retrying with the same `invoice` will not double-book a parcel.

---

### Code Examples

#### cURL
```bash
curl -X POST https://portal.packzy.com/api/v1/create_order \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Secret-Key: YOUR_SECRET_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice": "ORD-10231",
    "recipient_name": "Jahid Hasan",
    "recipient_phone": "01712345678",
    "recipient_address": "House 14, Road 7, Dhanmondi, Dhaka",
    "cod_amount": 1060,
    "note": "Call before delivery"
  }'
```

#### PHP (Laravel HTTP Client)
```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'Api-Key'    => 'YOUR_API_KEY',
    'Secret-Key' => 'YOUR_SECRET_KEY',
    'Content-Type' => 'application/json',
])->post('https://portal.packzy.com/api/v1/create_order', [
    'invoice'           => 'ORD-10231',
    'recipient_name'    => 'Jahid Hasan',
    'recipient_phone'   => '01712345678',
    'recipient_address' => 'House 14, Road 7, Dhanmondi, Dhaka',
    'cod_amount'        => 1060,
    'note'              => 'Call before delivery',
]);

if ($response->successful() && $response->json('status') === 200) {
    $consignmentId = $response->json('consignment.consignment_id');
    $trackingCode  = $response->json('consignment.tracking_code');
    $status        = $response->json('consignment.status'); // "in_review"
}
```

#### Node.js (Fetch)
```javascript
const response = await fetch('https://portal.packzy.com/api/v1/create_order', {
  method: 'POST',
  headers: {
    'Api-Key': 'YOUR_API_KEY',
    'Secret-Key': 'YOUR_SECRET_KEY',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    invoice: 'ORD-10231',
    recipient_name: 'Jahid Hasan',
    recipient_phone: '01712345678',
    recipient_address: 'House 14, Road 7, Dhanmondi, Dhaka',
    cod_amount: 1060,
    note: 'Call before delivery',
  }),
});

const data = await response.json();
console.log('Consignment ID:', data.consignment?.consignment_id);
console.log('Tracking Code:', data.consignment?.tracking_code);
```

#### Python (Requests)
```python
import requests

url = "https://portal.packzy.com/api/v1/create_order"
headers = {
    "Api-Key": "YOUR_API_KEY",
    "Secret-Key": "YOUR_SECRET_KEY",
    "Content-Type": "application/json",
}
payload = {
    "invoice": "ORD-10231",
    "recipient_name": "Jahid Hasan",
    "recipient_phone": "01712345678",
    "recipient_address": "House 14, Road 7, Dhanmondi, Dhaka",
    "cod_amount": 1060,
    "note": "Call before delivery",
}

response = requests.post(url, json=payload, headers=headers)
data = response.json()
print("Booked Consignment:", data.get("consignment", {}).get("consignment_id"))
```

---

### Successful Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "message": "Order created successfully",
  "consignment": {
    "consignment_id": 9845210,
    "invoice": "ORD-10231",
    "tracking_code": "ST-9845210",
    "recipient_name": "Jahid Hasan",
    "recipient_phone": "01712345678",
    "recipient_address": "House 14, Road 7, Dhanmondi, Dhaka",
    "cod_amount": 1060,
    "status": "in_review",
    "note": "Call before delivery",
    "created_at": "2026-10-27T10:55:00+06:00",
    "updated_at": "2026-10-27T10:55:00+06:00"
  }
}
```

---

## 📦 2. Bulk Order Booking (`POST /create_order/bulk-order`)

Book up to **500 parcels** in a single API request.

### Endpoint
```http
POST https://portal.packzy.com/api/v1/create_order/bulk-order
```

### Request Payload
```json
{
  "data": [
    {
      "invoice": "ORD-9001",
      "recipient_name": "Kamal Hossain",
      "recipient_phone": "01811223344",
      "recipient_address": "Mirpur-10, Dhaka",
      "cod_amount": 1500,
      "note": "Fragile"
    },
    {
      "invoice": "ORD-9002",
      "recipient_name": "Sadia Islam",
      "recipient_phone": "01999887766",
      "recipient_address": "GEC Circle, Chittagong",
      "cod_amount": 750,
      "note": "Morning delivery"
    }
  ]
}
```

---

## 📦 3. Bulk Order Extended (`POST /create_order/bulk-order/extended`)

The same as bulk-order, but returns **detailed per-field validation error messages** instead of numeric error codes when individual items fail validation.

### Endpoint
```http
POST https://portal.packzy.com/api/v1/create_order/bulk-order/extended
```
