# 🐝 Carrybee Delivery Developer API Suite (v2.0)

Official developer documentation, OpenAPI 3.0 specification, Postman collection, and multi-language SDKs for **Carrybee Delivery** (API v2.0), Bangladesh's next-generation e-commerce logistics, reverse pickup, and exchange fulfillment platform.

> **Maintained by:** [Refat Rahman](https://refat.ovh) ([@refatbd](https://github.com/refatbd))  
> **API Version:** Carrybee API `v2.0`  
> **Interactive API Reference:** [Open Carrybee Scalar Console](../../api-reference-carrybee.html)

---

## ⚡ Environments & Base URLs

Carrybee provides two completely isolated environments with dedicated base URLs:

| Environment | Base URL | Usage |
| :--- | :--- | :--- |
| **Sandbox (Testing)** | `https://sandbox.carrybee.com` | Safe testing playground. Generates mock parcels, test tracking, and sandbox webhooks. |
| **Production (Live)** | `https://developers.carrybee.com` | Live logistics operations with physical courier dispatch. |

---

## 🔐 Triple-Header Authentication Scheme

Carrybee authenticates every HTTP request using three environment-specific credentials issued per business:

```http
Client-ID: <your_client_id>
Client-Secret: <your_client_secret>
Client-Context: <your_client_context>
Content-Type: application/json
Accept: application/json
```

> [!WARNING]
> Keep your `Client-Secret` secure on your server. Never expose it in client-side Single Page Applications (SPAs), mobile app bundles, or public repositories.

---

## 📚 Documentation Chapters

| Chapter | Title | Focus Areas |
| :---: | :--- | :--- |
| **01** | [Getting Started & Authentication](01-getting-started.md) | Triple-header setup, sandbox credentials, error responses |
| **02** | [Location & Coverage](02-location-and-coverage.md) | Cities, zones, areas, address parser, and area text suggestions |
| **03** | [Stores & Warehouses](03-stores-and-warehouses.md) | Creating pickup stores, store listing, default pickup/return |
| **04** | [Orders & Consignments](04-orders-and-booking.md) | Single order placement, batch orders, tracking details, cancellation |
| **05** | [Reverse Pickup & Exchange](05-reverse-pickup-and-exchange.md) | Standalone reverse pickup, order-linked returns, exchange orders (`EX...`) |
| **06** | [Webhooks & Echo Handshake](06-webhooks-and-handshake.md) | Webhook setup, 202 Echo Handshake, `X-CB-Webhook-Integration-Header`, 24 Events |
| **07** | [Status Codes & Reference](07-status-reference.md) | Complete state machine transitions, conventions, delivery types |

---

## 🛠️ Ready-to-Use SDKs & Examples

- **cURL Shell Scripts**: [`sdks-and-examples/carrybee/curl/`](../../sdks-and-examples/carrybee/curl/)
- **PHP 8.x Client**: [`sdks-and-examples/carrybee/php/`](../../sdks-and-examples/carrybee/php/)
- **Node.js (Async/Await)**: [`sdks-and-examples/carrybee/nodejs/`](../../sdks-and-examples/carrybee/nodejs/)
- **Python (Requests)**: [`sdks-and-examples/carrybee/python/`](../../sdks-and-examples/carrybee/python/)
- **Postman Collection**: [`postman/Carrybee_Courier_API_v2.postman_collection.json`](../../postman/Carrybee_Courier_API_v2.postman_collection.json)
- **OpenAPI 3.0 Spec**: [`openapi/carrybee-openapi.yaml`](../../openapi/carrybee-openapi.yaml)
