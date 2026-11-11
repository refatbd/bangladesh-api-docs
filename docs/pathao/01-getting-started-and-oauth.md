# 01. Getting Started & OAuth 2.0 Authentication

Pathao Courier (Aladdin API v1) enforces standard **OAuth 2.0** security protocols. To interact with any logistics endpoint, an application must obtain an access token and cache it for its 5-day lifecycle.

---

## 🌐 Environments & Base URLs

| Environment | Base URL | Note |
| :--- | :--- | :--- |
| **Sandbox (Testing)** | `https://courier-api-sandbox.pathao.com` | Safe testing playground. Generates mock orders and simulated tracking. |
| **Production (Live)** | `https://api-hermes.pathao.com` | Real merchant logistics network with actual rider dispatches. |

---

## 🔑 Sandbox Credentials

Pathao provides public sandbox credentials for immediate developer onboarding:

```ini
base_url=https://courier-api-sandbox.pathao.com
client_id=7N1aMJQbWm
client_secret=wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39
username=test@pathao.com
password=lovePathao
grant_type=password
```

For production, log into [merchant.pathao.com](https://merchant.pathao.com/courier/developer-api), navigate to **Developer APIs**, and click **Generate Credentials**.

---

## 1. Issue Access Token (Password Grant)

Exchange merchant login credentials for an access token and refresh token.

- **Method:** `POST`
- **Endpoint:** `/aladdin/api/v1/issue-token`

### Request Parameters

| Field Name | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `client_id` | `string` | **Yes** | Your application Client ID issued by Pathao. |
| `client_secret` | `string` | **Yes** | Your confidential Client Secret. |
| `grant_type` | `string` | **Yes** | Must be set to literal string `"password"`. |
| `username` | `string` | **Yes** | Merchant login email address. |
| `password` | `string` | **Yes** | Merchant account password. |

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/issue-token' \
  --header 'Content-Type: application/json' \
  --data-raw '{
    "client_id": "7N1aMJQbWm",
    "client_secret": "wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39",
    "grant_type": "password",
    "username": "test@pathao.com",
    "password": "lovePathao"
  }'
```

### Sample Response (`200 OK`)

```json
{
  "token_type": "Bearer",
  "expires_in": 432000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIs...",
  "refresh_token": "def5020059c47087e35b7e87b7a5840d..."
}
```

---

## 2. Issue Access Token from Refresh Token

Renew an expired token without prompting for the user's password.

- **Method:** `POST`
- **Endpoint:** `/aladdin/api/v1/issue-token`

### Request Parameters

| Field Name | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `client_id` | `string` | **Yes** | Your Client ID. |
| `client_secret` | `string` | **Yes** | Your Client Secret. |
| `grant_type` | `string` | **Yes** | Must be set to literal string `"refresh_token"`. |
| `refresh_token` | `string` | **Yes** | The refresh token obtained from the previous issuance. |

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/issue-token' \
  --header 'Content-Type: application/json' \
  --data-raw '{
    "client_id": "7N1aMJQbWm",
    "client_secret": "wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39",
    "grant_type": "refresh_token",
    "refresh_token": "YOUR_REFRESH_TOKEN_HERE"
  }'
```

---

## 🛡️ Best Practice: Token Storage & Caching Strategy

The access token has an extraordinarily generous lifespan of **432,000 seconds (5 full days)**. 

> [!CAUTION]
> **Do not request a new token on every API call!** Repeatedly requesting tokens will trigger Pathao's rate limiters and degrade order placement latency.

### Recommended Architecture

```text
[ Incoming Order ]
         │
         ▼
[ Check Redis / Cache for 'pathao_token' ]
         ├── If Found & Valid (>5 min remaining) ──► Use Cached Token
         │
         └── If Expired / Missing
                   │
                   ▼
         [ Call /issue-token (refresh_token or password) ]
                   │
                   ▼
         [ Save to Cache with TTL: 430000s ]
                   │
                   ▼
         [ Proceed with Order Request ]
```
