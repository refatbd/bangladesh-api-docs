# 01. Getting Started & Authentication

Paperfly Courier exposes RESTful JSON endpoints hosted at `https://api.paperfly.com.bd`. This guide explains how to authenticate your API requests using HTTP Basic Authentication and the mandatory Paperfly API key header.

---

## 🔑 Merchant Credentials

Your API credentials are the same credentials you use to log in to the official [Paperfly Merchant Panel (Go)](https://go.paperfly.com.bd):

1. **Username**: Merchant Panel User Name (e.g. `sellerone`).
2. **Password**: Merchant Panel Account Password.

---

## 📋 Required HTTP Headers

Every request dispatched to Paperfly's API must include two authentication headers:

```http
Authorization: Basic <base64_encoded_credentials>
paperflykey: Paperfly_~La?Rj73FcLm
Content-Type: application/json
Accept: application/json
```

### 1. HTTP Basic Authorization Header

Constructed by encoding `username:password` in Base64:

```bash
# Example generating Basic Auth in bash:
echo -n "sellerone:your_password" | base64
# Output: c2VsbGVyb25lOnlvdXJfcGFzc3dvcmQ=
```

Your HTTP header will look like:
`Authorization: Basic c2VsbGVyb25lOnlvdXJfcGFzc3dvcmQ=`

### 2. Mandatory `paperflykey` Header

Paperfly requires a static system key passed in the header:

```http
paperflykey: Paperfly_~La?Rj73FcLm
```

> [!IMPORTANT]
> If the `paperflykey` header is missing or mistyped, Paperfly will reject the request with an HTTP 401 or invalid access response.

---

## ⚡ Complete Anatomy of an API Call (cURL)

```bash
curl --location 'https://api.paperfly.com.bd/merchant/api/service/new_order_v2.php' \
  --header 'Content-Type: application/json' \
  --header 'paperflykey: Paperfly_~La?Rj73FcLm' \
  --header 'Authorization: Basic YOUR_BASE64_CREDENTIALS' \
  --data '{
    "merchantOrderReference": "INV-2026-01",
    "storeName": "Main Store",
    "productBrief": "Cotton T-Shirt",
    "packagePrice": "1200",
    "max_weight": "0.5",
    "customerName": "Kamrul Hasan",
    "customerAddress": "Banani, Dhaka",
    "customerPhone": "01711223344"
  }'
```

---

## 📞 Support & Helplines

If you encounter account approval delays or need routing clarification, Paperfly provides direct support channels:
- **Relationship Manager (RM)**: `01822061509` (Bokhtar Ashik)
- **Central Helpline**: `09613 300 800`
