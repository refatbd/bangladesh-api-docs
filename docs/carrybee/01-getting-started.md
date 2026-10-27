# 01. Getting Started & Authentication

Carrybee Delivery Developers' API v2.0 is a modern RESTful JSON service. Every request requires authentication via three custom headers issued through your merchant dashboard.

---

## 🌐 Environments & Base URLs

| Environment | Base URL | Usage |
| :--- | :--- | :--- |
| **Sandbox** | `https://sandbox.carrybee.com` | Safe integration sandbox. Generates dummy orders, test consignments, and webhook mocks. |
| **Production** | `https://developers.carrybee.com` | Live logistics operations with physical rider collections and delivery. |

---

## 🔐 Authentication Scheme: Triple Headers

Every HTTP request must pass three environment-specific identifiers:

```http
Client-ID: <your_client_id>
Client-Secret: <your_client_secret>
Client-Context: <your_client_context>
Content-Type: application/json
Accept: application/json
```

1. **Client-ID**: Unique business identifier.
2. **Client-Secret**: Confidential signature key. Keep this strictly server-side.
3. **Client-Context**: Context token linking your merchant store profile.

---

## ⚠️ Consistent Error Response Formats

Carrybee enforces a predictable response envelope across all HTTP endpoints:

### Standard Error (4xx / 5xx)

```json
{
  "error": true,
  "message": "Store not found or deactivated"
}
```

### Validation Error (`422 Unprocessable Entity`)

Returned when parameters fail schema, phone length, or weight validation rules:

```json
{
  "error": true,
  "message": "Validation error",
  "causes": {
    "recipient_phone": [
      {
        "type": "required",
        "attribute": {}
      }
    ],
    "item_weight": [
      {
        "type": "max",
        "attribute": { "max": 25000 }
      }
    ]
  }
}
```

---

## 💡 Quick Connection Test

The easiest way to verify that your headers and credentials are functional is to query the city directory:

```bash
curl --location 'https://sandbox.carrybee.com/api/v2/cities' \
  --header 'Client-ID: YOUR_CLIENT_ID' \
  --header 'Client-Secret: YOUR_CLIENT_SECRET' \
  --header 'Client-Context: YOUR_CLIENT_CONTEXT'
```

If your credentials are valid, Carrybee will respond with `200 OK` and a list of serviceable cities.
