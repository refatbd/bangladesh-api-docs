# 06. Delivery Risk Fraud Check & Thanas Directory

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 🛡️ 1. Customer Fraud / Delivery Risk Score (`GET /fraud_check/score/{phone}`)

Steadfast maintains a centralized cross-merchant database of customer delivery histories across Bangladesh. This endpoint returns how many parcels the customer successfully received, how many were returned (RTO), and whether other merchants flagged the phone number as fraudulent.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/fraud_check/score/{phone}
Api-Key: YOUR_API_KEY
Secret-Key: YOUR_SECRET_KEY
```

### Parameters
| Parameter | Type | Required | Description |
| :--- | :--- | :---: | :--- |
| `phone` | String | **Yes** | 11-digit Bangladeshi mobile number (e.g. `01712345678`). |

### cURL
```bash
curl -X GET https://portal.packzy.com/api/v1/fraud_check/score/01712345678 \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Secret-Key: YOUR_SECRET_KEY" \
  -H "Accept: application/json"
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "phone": "01712345678",
  "total_parcels": 48,
  "delivered_parcels": 44,
  "cancelled_parcels": 4,
  "success_ratio": "91.67%",
  "risk_level": "low",
  "merchant_reports": 0
}
```

> [!TIP]
> **E-Commerce Use Case**: Before confirming high-value Cash on Delivery (COD) orders, query this endpoint. If `success_ratio` is below 60% or `risk_level` is `high`, ask the customer for partial advance payment via bKash/Nagad before shipping!

---

## 🏛️ 2. Police Stations / Thanas Directory (`GET /police_stations`)

Returns the complete list of all delivery thanas and police stations covered by Steadfast across all 64 districts of Bangladesh.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/police_stations
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "data": [
    {
      "id": 1,
      "thana": "Dhanmondi",
      "district": "Dhaka",
      "division": "Dhaka"
    },
    {
      "id": 2,
      "thana": "Uttara",
      "district": "Dhaka",
      "division": "Dhaka"
    },
    {
      "id": 142,
      "thana": "Panchlaish",
      "district": "Chittagong",
      "division": "Chittagong"
    }
  ]
}
```
