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
| **Pathao Courier** *(Coming Soon)* | Logistics / On-Demand | `https://api-hermes.pathao.com` | - | Planned | - | ⏳ Queued |
| **RedX Logistics** *(Coming Soon)* | Logistics / Fulfillment | `https://openapi.redx.com.bd` | - | Planned | - | ⏳ Queued |
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
├── docs/
│   └── steadfast/                     # Steadfast Courier Documentation Suite
│       ├── README.md                  # Master Overview & Quick Links
│       ├── 01-getting-started.md      # Base URL, Authentication, Rate Limits
│       ├── 02-booking-parcels.md      # Single & Bulk Parcel Creation
│       ├── 03-tracking-and-status.md  # Tracking & Parcel Status Queries
│       ├── 04-pickups-and-returns.md  # Rider Pickup & Return Requests
│       ├── 05-balance-and-payments.md # Wallet Balance & COD Remittance Payouts
│       ├── 06-fraud-check-and-thanas.md # Customer Fraud Score & Thana Directory
│       ├── 07-status-code-reference.md # Complete State Machine & HTTP Codes
│       └── 08-webhooks.md             # Real-Time Event Notifications
├── sdks-and-examples/
│   └── steadfast/                     # Production Ready Clients
│       ├── curl/                      # Direct Bash/Shell Scripts
│       ├── php/                       # Standalone PHP 8.x / Laravel Client
│       ├── nodejs/                    # Modern Async/Await Node.js Client
│       └── python/                    # Python 3 Requests Client
├── postman/
│   └── Steadfast_Courier_API_v1.postman_collection.json
└── openapi/
    └── steadfast-openapi.yaml
```

---

## 🤝 Contributing

Contributions are warmly welcomed! If you have documentation, SDKs, or Postman collections for other Bangladeshi APIs, please read [CONTRIBUTING.md](CONTRIBUTING.md) and submit a Pull Request.

---

## 📄 License

This documentation and code collection is open-sourced under the [MIT License](LICENSE) &copy; 2026 **Refat Rahman**.
