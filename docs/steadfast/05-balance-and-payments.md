# 05. Wallet Balance & COD Settlements

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 💰 1. Get Wallet Balance (`GET /get_balance`)

Retrieves the total COD balance Steadfast currently owes your merchant profile.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/get_balance
Api-Key: YOUR_API_KEY
Secret-Key: YOUR_SECRET_KEY
```

### cURL
```bash
curl -X GET https://portal.packzy.com/api/v1/get_balance \
  -H "Api-Key: YOUR_API_KEY" \
  -H "Secret-Key: YOUR_SECRET_KEY" \
  -H "Accept: application/json"
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "current_balance": 14500.50
}
```

---

## 🏦 2. Get Payouts List (`GET /payments`)

Returns payouts that Steadfast has deposited into your bank or mobile wallet, newest first, **10 items per page**.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/payments?page=1
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "data": [
    {
      "payment_id": 54210,
      "amount": 28450.00,
      "payment_method": "Bank Transfer (BEFTN)",
      "transaction_ref": "TRX-ST-99812",
      "status": "completed",
      "paid_at": "2026-10-25 18:00:00"
    }
  ]
}
```

---

## 🧾 3. Get Single Payout Details (`GET /payments/{payment_id}`)

Retrieves an individual payout along with the detailed list of **every parcel it settled**, including COD collected and delivery fees deducted.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/payments/54210
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "payment": {
    "payment_id": 54210,
    "amount": 28450.00,
    "parcels": [
      {
        "consignment_id": 9845210,
        "invoice": "ORD-10231",
        "cod_collected": 1060.00,
        "delivery_charge": 60.00,
        "cod_fee": 10.60,
        "net_amount": 989.40
      }
    ]
  }
}
```
