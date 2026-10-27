# 🇧🇩 Bangladesh API Hub & Developer Ecosystem

[![CI & GitHub Pages Deployment](https://github.com/refatbd/bangladesh-api-docs/actions/workflows/ci-deploy.yml/badge.svg)](https://github.com/refatbd/bangladesh-api-docs/actions/workflows/ci-deploy.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Maintained by Refat Rahman](https://img.shields.io/badge/Author-Refat%20Rahman-orange?style=flat&logo=github)](https://github.com/refatbd)
[![Website](https://img.shields.io/badge/Website-refat.ovh-green)](https://refat.ovh)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

An open-source, production-grade API documentation repository, SDK collection, Postman suite, OpenAPI specifications, and universal logistics facade for Bangladeshi Couriers, FinTech Gateways, and E-Commerce Services.

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

## 🛠️ Interactive Developer Playgrounds

This portal includes built-in interactive tools accessible via the documentation viewer (`index.html`):

1. **[Multi-Courier Shipping Rate & COD Calculator](docs/tools/rate-calculator.md)**: Real-time reactive calculator comparing net shipping charges, 1% COD fees, and delivery SLA across Steadfast, RedX, Pathao, Paperfly, Carrybee, and eCourier side-by-side.
2. **[Phone Number Fraud Risk Inspector](docs/tools/fraud-checker.md)**: Real-time customer delivery success ratio, return rate, and fraud risk score verification sandbox (powered by Steadfast & eCourier APIs).
3. **Global Command Palette (`Ctrl + K` / `⌘K`)**: Instant client-side full-text search indexing across 55+ chapters and 7 providers with category filters and keyboard navigation.

---

## 📦 Unified Multi-Courier Engine (`refatbd/bangladesh-courier`)

Every Bangladeshi courier invents their own API naming conventions (grams vs kilograms, varying status codes, custom webhook signatures). The **Unified Courier Engine** normalizes all 6 major logistics services into a single PSR-4 PHP and Node.js interface:

### PHP Installation
```bash
composer require refatbd/bangladesh-courier
```

### Standard Usage
```php
use Refatbd\Courier\Courier;
use Refatbd\Courier\DTO\OrderRequest;

// Factory instantiate any provider
$courier = Courier::provider('steadfast', [
    'api_key'    => env('STEADFAST_API_KEY'),
    'secret_key' => env('STEADFAST_SECRET_KEY'),
]);

// Book parcel with normalized payload
$order = $courier->createOrder(new OrderRequest([
    'invoice_id'        => 'INV-9021',
    'recipient_name'    => 'Refat Rahman',
    'recipient_phone'   => '01700000000',
    'recipient_address' => 'House 12, Road 4, Dhanmondi, Dhaka',
    'delivery_zone'     => 'inside_dhaka',
    'amount_to_collect' => 2500,  // COD in BDT
    'weight_kg'         => 1.5,   // Automatically converted to 1500g for RedX/Paperfly
]));

echo $order->tracking_code; // Standardized tracking identifier
echo $order->status;        // Canonical status: 'pending', 'in_transit', 'delivered'
```

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
| **[Nagad Direct](docs/nagad/README.md)** | MFS / Payment Gateway | `https://api.mynagad.com/api/dfs` | **3 (Dual RSA)** | ✅ Included | PHP, JS, Python, cURL | 🟢 **Complete** |

---

## 📂 Repository Structure

```text
bangladesh-api-docs/
├── README.md                          # Master Directory & Developer Hub
├── LICENSE                            # MIT License
├── CONTRIBUTING.md                    # Community Contribution Standards
├── index.html                         # VitePress/Supabase Grade Hub Viewer (with Ctrl+K)
├── .spectral.yaml                     # Spectral OpenAPI 3.0 Linting Rules
├── .github/
│   └── workflows/
│       └── ci-deploy.yml              # GitHub Actions CI/CD & Pages Deploy Pipeline
├── packages/
│   └── unified-courier/               # refatbd/bangladesh-courier Package Suite
│       ├── composer.json              # PSR-4 Autoloading Definition
│       ├── package.json               # Node.js (@refatbd/bd-courier) Definition
│       ├── index.js                   # Node.js ESM Entry Point
│       ├── src/
│       │   ├── Courier.php            # Unified Facade & Factory Manager
│       │   ├── Contracts/             # CourierAdapterInterface.php
│       │   ├── Enums/                 # CourierStatus.php (Normalized Enums)
│       │   ├── DTO/                   # Request & Response Data Transfer Objects
│       │   └── Adapters/              # Steadfast, RedX, Pathao, Paperfly, Carrybee, eCourier
│       └── tests/
│           └── test-unified.php       # Automated PHP Test Suite (11 Assertions Passed)
├── docs/
│   ├── tools/                         # Interactive Developer Playgrounds
│   │   ├── rate-calculator.md         # Multi-Courier Shipping Rate & COD Calculator
│   │   ├── fraud-checker.md           # Phone Number Fraud Risk Inspector
│   │   └── unified-courier.md         # Unified Engine Architecture & Guide
│   ├── steadfast/                     # Steadfast Courier (18 Endpoints)
│   ├── redx/                          # RedX Delivery (11 Endpoints)
│   ├── pathao/                        # Pathao Courier (10 Endpoints)
│   ├── paperfly/                      # Paperfly Courier (4 Endpoints + 14 Events)
│   ├── carrybee/                      # Carrybee Delivery (14 Endpoints + 24 Events)
│   ├── ecourier/                      # eCourier Courier (21 Endpoints)
│   └── nagad/                         # Nagad Direct PG (3 Endpoints, Dual RSA-2048)
├── sdks-and-examples/                 # Standalone Clients in cURL, PHP, Node, Python
├── postman/                           # Importable Postman Collections for all 7 Providers
└── openapi/                           # OpenAPI 3.0 / Swagger YAML Specifications
```

---

## 📄 License

This documentation and code ecosystem is open-sourced under the [MIT License](LICENSE) &copy; 2026 **Refat Rahman**.
