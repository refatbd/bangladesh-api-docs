# 🟢 Pathao Courier Developer API Suite (Aladdin API v1)

Official developer documentation, OpenAPI 3.0 specification, Postman collection, and multi-language SDKs for **Pathao Courier** (Aladdin API v1), Bangladesh's premier on-demand and next-day logistics network.

> **Maintained by:** [Refat Rahman](https://refat.ovh) ([@refatbd](https://github.com/refatbd))  
> **API Version:** Aladdin API `v1`  
> **Interactive API Reference:** [Open Pathao Scalar Console](../../api-reference-pathao.html)

---

## ⚡ Environments & Credentials

Pathao utilizes OAuth 2.0 password and refresh token grants. Sandbox credentials for staging are provided out-of-the-box by Pathao:

| Environment | Base URL | Client ID / Credentials |
| :--- | :--- | :--- |
| **Sandbox (Testing)** | `https://courier-api-sandbox.pathao.com` | `client_id`: `7N1aMJQbWm`<br>`client_secret`: `wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39`<br>`username`: `test@pathao.com`<br>`password`: `lovePathao` |
| **Production (Live)** | `https://api-hermes.pathao.com` | Generated directly from [Pathao Merchant Panel](https://merchant.pathao.com/courier/developer-api) |

---

## 🔐 OAuth 2.0 Authentication Flow

Pathao operates under standard OAuth 2.0 with a **5-day token lifespan** (`expires_in: 432000` seconds):

1. **Issue Access Token**: Send `POST /aladdin/api/v1/issue-token` with `grant_type: password` along with merchant client credentials.
2. **Persistent Storage**: Cache the returned `access_token` and `refresh_token` in your database or Redis cache.
3. **Authorized Requests**: Pass the bearer token in every subsequent request header:
   ```http
   Authorization: Bearer <access_token>
   Content-Type: application/json
   Accept: application/json
   ```
4. **Token Refresh**: Before expiration or upon receiving HTTP 401, execute `grant_type: refresh_token` to renew access seamlessly without requiring merchant password re-entry.

---

## 📚 Documentation Chapters

| Chapter | Title | Focus Areas |
| :---: | :--- | :--- |
| **01** | [Getting Started & OAuth 2.0](01-getting-started-and-oauth.md) | Password grant, refresh token flow, token caching strategy |
| **02** | [Stores & Warehouses](02-stores-and-warehouses.md) | Warehouse creation (`POST /stores`), store listing, default store settings |
| **03** | [City, Zone & Area Hierarchy](03-city-zone-area-hierarchy.md) | 3-tier geographical directory (`/city-list`, `/zone-list`, `/area-list`) |
| **04** | [Creating Orders](04-creating-orders.md) | Single order booking (`POST /orders`), delivery types (48 vs 12), COD collection |
| **05** | [Bulk Consignment Orders](05-bulk-orders.md) | High-volume batch order dispatch (`POST /orders/bulk`) with HTTP 202 queuing |
| **06** | [Tracking & Order Status](06-order-tracking-and-status.md) | Tracking consignment summary (`GET /orders/:id/info`) |
| **07** | [Price Calculation Plan](07-price-calculation-plan.md) | Real-time shipping cost & COD percentage quotation (`POST /merchant/price-plan`) |
| **08** | [Status Lifecycle & Reference](08-status-lifecycle-and-webhooks.md) | Order state machine, delivery types, item types, and error codes |

---

## 🛠️ Ready-to-Use SDKs & Examples

- **cURL Shell Scripts**: [`sdks-and-examples/pathao/curl/`](../../sdks-and-examples/pathao/curl/)
- **PHP 8.x / Laravel Client**: [`sdks-and-examples/pathao/php/`](../../sdks-and-examples/pathao/php/)
- **Node.js (Async/Await)**: [`sdks-and-examples/pathao/nodejs/`](../../sdks-and-examples/pathao/nodejs/)
- **Python (Requests)**: [`sdks-and-examples/pathao/python/`](../../sdks-and-examples/pathao/python/)
- **Postman Collection**: [`postman/Pathao_Courier_API_v1.postman_collection.json`](../../postman/Pathao_Courier_API_v1.postman_collection.json)
- **OpenAPI 3.0 Spec**: [`openapi/pathao-openapi.yaml`](../../openapi/pathao-openapi.yaml)
