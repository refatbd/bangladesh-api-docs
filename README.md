# 🇧🇩 Bangladesh API Hub & Developer Documentation

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Maintained by Refat Rahman](https://img.shields.io/badge/Author-Refat%20Rahman-orange?style=flat&logo=github)](https://github.com/refatbd)
[![Website](https://img.shields.io/badge/Website-refat.ovh-green)](https://refat.ovh)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

An open-source, production-grade API documentation repository, SDK collection, Postman suite, and OpenAPI specifications for Bangladeshi Couriers, FinTech Gateways, and Logistics Providers.

---

## 👨‍💻 Developer & Maintainer

| Field | Contact / Link |
| :--- | :--- |
| **Name** | **Refat Rahman** |
| **Website** | [refat.ovh](https://refat.ovh) |
| **GitHub** | [@refatbd](https://github.com/refatbd) |
| **Facebook** | [fb.com/rjrefat](https://www.facebook.com/rjrefat/) |
| **Instagram** | [@refat_official](https://www.instagram.com/refat_official) |
| **Telegram** | [@refatbd](https://t.me/refatbd) |

---

## 🚀 Available API Documentations

| Provider | Category | Base URL | Endpoints | Postman | SDKs | Status |
| :--- | :--- | :--- | :---: | :---: | :---: | :---: |
| **[Steadfast Courier](docs/steadfast/README.md)** | Logistics / COD | `https://portal.packzy.com/api/v1` | **18** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |
| **[RedX Delivery](docs/redx/README.md)** | Logistics / Fulfillment | `https://openapi.redx.com.bd` | **11** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |
| **[Pathao Courier](docs/pathao/README.md)** | Logistics / On-Demand | `https://api-hermes.pathao.com` | **10** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |
| **[Paperfly Courier](docs/paperfly/README.md)** | Logistics / Doorstep & Exchange | `https://api.paperfly.com.bd` | **4 + 14 Events** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |
| **[Carrybee Courier](docs/carrybee/README.md)** | Logistics / Express & Exchange | `https://developers.carrybee.com` | **14 + 24 Events** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |
| **[eCourier Courier](docs/ecourier/README.md)** | Logistics / Reseller & VAS | `https://backoffice.ecourier.com.bd/api` | **21** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |
| **bKash Tokenized** *(Coming Soon)* | MFS / Payment Gateway | `https://tokenized.pay.bka.sh/v1.2.0-beta` | - | Planned | - | ⏳ Queued |
| **Nagad Direct** *(Coming Soon)* | MFS / Payment Gateway | `https://api.mynagad.com` | - | Planned | - | ⏳ Queued |

---

## 📦 What's Inside Each API Suite?

Every documented company in this repository includes:
1. **Full API Guides & References**: Complete breakdown of all available endpoints, query parameters, JSON request payloads, and status codes.
2. **Ready-to-Run Code Examples**: Standalone, copy-pasteable snippets in **cURL**, **PHP (Laravel)**, **JavaScript (Node.js)**, and **Python**.
3. **Official Postman Collection**: Importable `.postman_collection.json` with preconfigured variables and test assertions.
4. **OpenAPI 3.0 / Swagger Specifications**: Standard `.yaml` schema for automatic client SDK generation and Swagger UI visualization.
5. **Real-World Edge Cases**: Documented quirks, character stripping rules (e.g., Steadfast's sanitization of `{ } ; < > $`), rate limits, and failure fallback semantics.

---

## 📂 Repository Structure

```text
bangladesh-api-docs/
├── README.md                          # Master Directory & Hub
├── LICENSE                            # MIT License
├── CONTRIBUTING.md                    # Guidelines for Community Contributors
├── index.html                         # VitePress/Supabase Grade Hub Viewer
├── api-reference.html                 # Steadfast Scalar Interactive Reference
├── api-reference-redx.html            # RedX Delivery Scalar Interactive Reference
├── api-reference-pathao.html          # Pathao Courier Scalar Interactive Reference
├── api-reference-paperfly.html        # Paperfly Courier Scalar Interactive Reference
├── api-reference-carrybee.html        # Carrybee Courier Scalar Interactive Reference
├── api-reference-ecourier.html        # eCourier Courier Scalar Interactive Reference
├── docs/
│   ├── steadfast/                     # Steadfast Courier Documentation Suite (18 Endpoints)
│   │   ├── README.md
│   │   └── 01-getting-started.md ... 08-webhooks.md
│   ├── redx/                          # RedX Delivery Documentation Suite (11 Endpoints)
│   │   ├── README.md
│   │   └── 01-getting-started.md ... 08-status-and-error-codes.md
│   ├── pathao/                        # Pathao Courier Aladdin API v1 (10 Endpoints)
│   │   ├── README.md
│   │   └── 01-getting-started-and-oauth.md ... 08-status-lifecycle-and-webhooks.md
│   ├── paperfly/                      # Paperfly Courier Developer API v2 (4 Endpoints + 14 Events)
│   │   ├── README.md                  # Master Overview & Basic Auth Credentials
│   │   ├── 01-getting-started.md      # Basic Auth & paperflykey Header
│   │   ├── 02-order-creation.md       # Standard Consignment Placement
│   │   ├── 03-exchange-orders.md      # Simultaneous Exchange Deliveries
│   │   ├── 04-tracking-and-status.md  # Milestone Progression Status
│   │   ├── 05-cancellation.md         # Pre-Pickup Cancellation Rules
│   │   ├── 06-webhooks-and-events.md  # 14 Real-Time Webhook Subscriptions
│   │   └── 07-status-reference.md     # State Machine & Tracking Key Mapping
│   ├── carrybee/                      # Carrybee Delivery API v2.0 (14 Endpoints + 24 Events)
│   │   ├── README.md                  # Master Overview & Triple-Header Authentication
│   │   ├── 01-getting-started.md      # Triple-Header Auth, Sandboxes, Context Setup
│   │   ├── 02-location-and-coverage.md# Cities, Zones, Autocomplete & Address Resolution
│   │   ├── 03-stores-and-warehouses.md# Merchant Dispatch Locations & Store Creation
│   │   ├── 04-orders-and-booking.md   # Single Delivery, Bulk Async (202), Tracking & Cancellation
│   │   ├── 05-reverse-pickup-and-exchange.md # Customer Reverse Returns & Item Swap Exchanges
│   │   ├── 06-webhooks-and-handshake.md # 202 Accepted Echo Handshake & 24 Event Handlers
│   │   └── 07-status-reference.md     # Lifecycle State Machine, 422 Errors & Polling Fallback
│   └── ecourier/                      # eCourier Courier API v5.5 (21 Endpoints)
│       ├── README.md                  # Master Overview & Triple-Header Authentication
│       ├── 01-getting-started.md      # Triple-Header Auth, Base URLs & Connection Testing
│       ├── 02-coverage-and-geography.md # Cities, Thanas, Postcodes & Hub Routing (7 APIs)
│       ├── 03-package-selection.md    # Assigned Contracted Package Codes & Rate Tiers
│       ├── 04-order-placement.md      # Standard Order, COD, Exchange & Hub Delivery
│       ├── 05-reseller-and-child-orders.md # Child Vendor Payouts (Bank/bKash/Rocket Splitting)
│       ├── 06-tracking-labels-and-finance.md # Milestone Tracking, Label PDF & Payment Status
│       ├── 07-fraud-check-and-booster-sms.md # AI Fraud Return Check & Booster VAS Services
│       └── 08-status-and-error-reference.md # State Machine, HTTP Codes & 400 Validation Dictionary
├── sdks-and-examples/
│   ├── steadfast/                     # Steadfast Clients (cURL, PHP, Node, Python)
│   ├── redx/                          # RedX Clients (cURL, PHP, Node, Python)
│   ├── pathao/                        # Pathao Courier Clients (cURL, PHP, Node, Python)
│   ├── paperfly/                      # Paperfly Courier Clients (cURL, PHP, Node, Python)
│   ├── carrybee/                      # Carrybee Courier Clients (cURL, PHP, Node, Python)
│   └── ecourier/                      # eCourier Courier Clients
│       ├── curl/                      # Bash Scripts (order, track, fraud, city, label, payment)
│       ├── php/                       # EcourierClient.php (PHP 8.x) & example.php
│       ├── nodejs/                    # ecourier.js (Node 18+ fetch) & example.js
│       └── python/                    # ecourier.py (Requests) & example.py
├── postman/
│   ├── Steadfast_Courier_API_v1.postman_collection.json
│   ├── RedX_Courier_API_v1.postman_collection.json
│   ├── Pathao_Courier_API_v1.postman_collection.json
│   ├── Paperfly_Courier_API_v1.postman_collection.json
│   ├── Carrybee_Courier_API_v2.postman_collection.json
│   └── eCourier_Courier_API_v5.postman_collection.json
└── openapi/
    ├── steadfast-openapi.yaml
    ├── redx-openapi.yaml
    ├── pathao-openapi.yaml
    ├── paperfly-openapi.yaml
    ├── carrybee-openapi.yaml
    └── ecourier-openapi.yaml
```

---

## 🤝 Contributing

Contributions are warmly welcomed! If you have documentation, SDKs, or Postman collections for other Bangladeshi APIs, please read [CONTRIBUTING.md](CONTRIBUTING.md) and submit a Pull Request.

---

## 📄 License

This documentation and code collection is open-sourced under the [MIT License](LICENSE) &copy; 2026 **Refat Rahman**.
