# 🔴 RedX Delivery Developer API Suite (v1.0.0-beta)

Official developer guide, OpenAPI 3.0 schema, Postman suite, and multi-language SDKs for **RedX Delivery** (ShopUp Logistics), Bangladesh's leading nationwide parcel delivery network.

> **Maintained by:** [Refat Rahman](https://refat.ovh) ([@refatbd](https://github.com/refatbd))  
> **Target API Version:** `v1.0.0-beta`  
> **Interactive API Reference:** [Open RedX Scalar Console](../../api-reference-redx.html)

---

## ⚡ Environments & Base URLs

RedX provides separate endpoints for staging/experimentation and live production fulfillment:

| Environment | Base URL | Usage |
| :--- | :--- | :--- |
| **Sandbox (Testing)** | `https://sandbox.redx.com.bd/v1.0.0-beta` | Simulated testing, dummy parcel creation, no physical dispatch |
| **Production (Live)** | `https://openapi.redx.com.bd/v1.0.0-beta` | Live logistics operations, real courier pickups and COD settlement |

---

## 🔐 Authentication Scheme

RedX uses JWT (JSON Web Token) bearer tokens generated directly from the RedX Merchant Dashboard. Every API request must pass the token via a custom HTTP header:

```http
API-ACCESS-TOKEN: Bearer <your_jwt_token>
Content-Type: application/json
Accept: application/json
```

> [!IMPORTANT]
> Note the non-standard header name: RedX expects `API-ACCESS-TOKEN: Bearer <token>` instead of the standard `Authorization: Bearer <token>`.

---

## 📚 Documentation Chapters

| Chapter | Title | Focus Areas |
| :---: | :--- | :--- |
| **01** | [Getting Started & Authentication](01-getting-started.md) | Merchant token generation, sandbox setup, headers, rate limits |
| **02** | [Parcels & Order Management](02-parcels-and-orders.md) | Single parcel creation (`POST /parcel`), update/cancel (`PATCH /parcels`), info query |
| **03** | [Tracking & Lifecycle Status](03-tracking-and-status.md) | Parcel tracking timeline with English & Bengali logs (`GET /parcel/track/:id`) |
| **04** | [Areas & Delivery Coverage](04-areas-and-coverage.md) | Complete 64 districts area directory, search by postal code & district name |
| **05** | [Pickup Stores](05-pickup-stores.md) | Multiple warehouse registration, store details, and default pickup assignment |
| **06** | [Charge Calculator](06-charge-calculator.md) | Dynamic delivery charge and Cash-on-Delivery (COD) cost estimation |
| **07** | [Real-Time Webhooks](07-webhooks-and-callbacks.md) | Automated webhook registration, payload verification, status dispatch |
| **08** | [Status Codes & Reference](08-status-and-error-codes.md) | State machine lifecycle, delivery types (`regular`, `exchange`, `reverse`, `partial`) |

---

## 🛠️ Ready-to-Use SDKs & Examples

Production-tested integration clients for major languages:

- **cURL Shell Scripts**: [`sdks-and-examples/redx/curl/`](../../sdks-and-examples/redx/curl/)
- **PHP 8.x / Laravel Client**: [`sdks-and-examples/redx/php/`](../../sdks-and-examples/redx/php/)
- **Node.js (Async/Await)**: [`sdks-and-examples/redx/nodejs/`](../../sdks-and-examples/redx/nodejs/)
- **Python (Requests)**: [`sdks-and-examples/redx/python/`](../../sdks-and-examples/redx/python/)
- **Postman Collection**: [`postman/RedX_Courier_API_v1.postman_collection.json`](../../postman/RedX_Courier_API_v1.postman_collection.json)
- **OpenAPI 3.0 Spec**: [`openapi/redx-openapi.yaml`](../../openapi/redx-openapi.yaml)
