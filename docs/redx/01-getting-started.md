# 01. Getting Started & Authentication

Welcome to the **RedX Delivery OpenAPI v1.0.0-beta** technical onboarding guide. This document details how to obtain your API credentials, configure the sandbox testing environment, and format your HTTP request headers.

---

## 🔑 Obtaining API Credentials

RedX uses JWT (JSON Web Token) bearer tokens issued per merchant store:

1. Log in to your merchant account at [redx.com.bd](https://redx.com.bd).
2. From the merchant navigation sidebar, click on **Developer APIs** (or visit `https://redx.com.bd/developer-api/`).
3. Under the **Sandbox** or **Production** section:
   - For **Sandbox**: Click **"Request token"** to generate an isolated testing token.
   - For **Production**: Click **"Request token"** under the live credentials block.
4. Copy the issued JWT token and store it securely in your server's `.env` or secrets vault.

> [!WARNING]
> Never expose your RedX JWT token in public GitHub repositories, client-side JavaScript, mobile apps, or frontend bundles. All courier operations must be orchestrated from your backend server.

---

## 🌐 Base URLs & Environments

| Environment | Endpoint URL | Behavior |
| :--- | :--- | :--- |
| **Sandbox** | `https://sandbox.redx.com.bd/v1.0.0-beta` | Staging database. Simulated orders, no physical rider dispatch. |
| **Production** | `https://openapi.redx.com.bd/v1.0.0-beta` | Live database. Generates actual logistics manifests, parcel labels, and rider pickup queues. |

---

## 📋 Required HTTP Headers

Unlike standard OAuth 2.0 implementations which use `Authorization: Bearer <token>`, RedX requires a **custom header**:

```http
API-ACCESS-TOKEN: Bearer <your_jwt_token>
Content-Type: application/json
Accept: application/json
```

### Complete Request Anatomy (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/pickup/stores' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' \
  --header 'Content-Type: application/json'
```

---

## 🚦 Common HTTP Response Codes

| HTTP Status | Meaning | Typical Cause |
| :---: | :--- | :--- |
| **`200 OK`** | Request Succeeded | The requested resource was retrieved or updated successfully. |
| **`201 Created`** | Resource Created | The parcel or pickup store was successfully created. |
| **`400 Bad Request`** | Malformed Request | Missing required parameter, invalid JSON formatting, or invalid field types. |
| **`401 Unauthorized`** | Authentication Failure | `API-ACCESS-TOKEN` header missing, token expired, or token signature invalid. |
| **`404 Not Found`** | Resource Not Found | Specified `tracking_id`, `area_id`, or `pickup_store_id` does not exist. |
| **`500 Server Error`** | Upstream Error | Internal system exception. Retry with exponential backoff. |

---

## 💡 Quick Health Check

The most lightweight way to test if your credentials and connection are functional is to query your pickup stores:

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/pickup/stores' \
  --header 'API-ACCESS-TOKEN: Bearer YOUR_TOKEN'
```

If your token is valid, you will receive a `200 OK` status with your registered pickup warehouse details.
