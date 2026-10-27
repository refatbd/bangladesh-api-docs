# 🦅 Paperfly Courier Developer API Suite (v2)

Official developer guide, OpenAPI 3.0 specification, Postman collection, and multi-language SDKs for **Paperfly Courier**, Bangladesh's pioneer high-capacity doorstep delivery and smart return network.

> **Maintained by:** [Refat Rahman](https://refat.ovh) ([@refatbd](https://github.com/refatbd))  
> **API Version:** Paperfly API `v2`  
> **Interactive API Reference:** [Open Paperfly Scalar Console](../../api-reference-paperfly.html)

---

## ⚡ API Endpoint & Authentication Scheme

Paperfly uses HTTP Basic Authentication combined with a mandatory custom platform API key:

| Component | Value / Description |
| :--- | :--- |
| **Base URL** | `https://api.paperfly.com.bd` |
| **Auth Type** | **HTTP Basic Authentication** (`Username`: Merchant Panel Username, `Password`: Merchant Panel Password) |
| **Mandatory Header** | `paperflykey: Paperfly_~La?Rj73FcLm` |
| **Content Type** | `application/json` |

```http
POST /merchant/api/service/new_order_v2.php HTTP/1.1
Host: api.paperfly.com.bd
Authorization: Basic <base64(username:password)>
paperflykey: Paperfly_~La?Rj73FcLm
Content-Type: application/json
```

---

## 📚 Documentation Chapters

| Chapter | Title | Focus Areas |
| :---: | :--- | :--- |
| **01** | [Getting Started & Authentication](01-getting-started.md) | Merchant panel credentials, `paperflykey` header, Basic Auth encoding |
| **02** | [Standard Order Creation](02-order-creation.md) | Single consignment placement (`POST /merchant/api/service/new_order_v2.php`) |
| **03** | [Exchange Order Processing](03-exchange-orders.md) | Replacement parcel creation (`orderType: "Exchange"`), simultaneous pickup |
| **04** | [Consignment Tracking](04-tracking-and-status.md) | Transit milestone history query (`POST /API-Order-Tracking`) |
| **05** | [Order Cancellation](05-cancellation.md) | Aborting uncollected shipments (`POST /api/v1/cancel-order`) |
| **06** | [Real-Time Webhooks](06-webhooks-and-events.md) | 14 event subscriptions across Lifecycle, Transit, Delivery & Returns |
| **07** | [Status Codes & Reference](07-status-reference.md) | State machine transitions, tracking key mappings, error resolutions |

---

## 🛠️ Ready-to-Use SDKs & Examples

- **cURL Shell Scripts**: [`sdks-and-examples/paperfly/curl/`](../../sdks-and-examples/paperfly/curl/)
- **PHP 8.x Client**: [`sdks-and-examples/paperfly/php/`](../../sdks-and-examples/paperfly/php/)
- **Node.js (Async/Await)**: [`sdks-and-examples/paperfly/nodejs/`](../../sdks-and-examples/paperfly/nodejs/)
- **Python (Requests)**: [`sdks-and-examples/paperfly/python/`](../../sdks-and-examples/paperfly/python/)
- **Postman Collection**: [`postman/Paperfly_Courier_API_v1.postman_collection.json`](../../postman/Paperfly_Courier_API_v1.postman_collection.json)
- **OpenAPI 3.0 Spec**: [`openapi/paperfly-openapi.yaml`](../../openapi/paperfly-openapi.yaml)
