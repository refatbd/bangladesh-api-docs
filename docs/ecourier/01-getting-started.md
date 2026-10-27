# 01. Getting Started & Authentication

This guide covers authenticating with the eCourier Merchant API v5.5, credential acquisition, environment configuration, and executing your initial connection ping.

---

## 1. Authentication Credentials

eCourier secures all merchant API operations using a triple-header credential model. Upon merchant account onboarding in the eCourier Backoffice portal, you will be issued three keys:

1. **`API-KEY`**: Public identifier for your merchant integration.
2. **`API-SECRET`**: Private cryptographically generated secret key.
3. **`USER-ID`**: Merchant backoffice user account identifier.

### Header Configuration

Every API request requires these three values present in the HTTP request headers:

| Header Name | Type | Description | Example |
| :--- | :--- | :--- | :--- |
| `API-KEY` | `string` | Your assigned merchant API key | `k9a8b7c6d5e4f3` |
| `API-SECRET` | `string` | Your assigned merchant API secret | `s1e2c3r4e5t6_key` |
| `USER-ID` | `string` | Your merchant login ID / username | `refat_merchant` |
| `Content-Type`| `string` | Mandatory request body serialization format | `application/json` |

---

## 2. Environments & Base URLs

eCourier maintains two separate physical environments:

```text
Staging (Sandbox): https://staging.ecourier.com.bd/api
Production (Live): https://backoffice.ecourier.com.bd/api
```

> [!NOTE]
> Staging credentials and production credentials are distinct. Ensure your environment variables isolate staging from production keys.

---

## 3. Initial Connectivity Ping (cURL)

Because eCourier does not have a dedicated `/ping` endpoint, the recommended lightweight method to verify your credentials is to request the active package list (`POST /packages`) or city list (`POST /city-list`).

```bash
curl -X POST "https://staging.ecourier.com.bd/api/packages" \
  -H "API-KEY: YOUR_API_KEY" \
  -H "API-SECRET: YOUR_API_SECRET" \
  -H "USER-ID: YOUR_USER_ID" \
  -H "Content-Type: application/json"
```

### Expected Successful Response (`200 OK`)

```json
[
  {
    "package_name": "SAMEDAY_Inside_120tk",
    "package_code": "#2505",
    "shipping_charge": "120",
    "weight": "Up To 500gm",
    "delivery_time": "Same Day(8Hr)",
    "coverage_id": "1",
    "coverage": "Inside Dhaka"
  },
  {
    "package_name": "NEXTDAY_Outside_150tk",
    "package_code": "#2506",
    "shipping_charge": "150",
    "weight": "Up To 1kg",
    "delivery_time": "Next Day(24hr)",
    "coverage_id": "2",
    "coverage": "Outside Dhaka"
  }
]
```

### Authentication Failure Response (`401 Unauthorized`)

If any of the three header credentials are incorrect, revoked, or missing, eCourier returns:

```json
{
  "errors": [
    "Unauthorized Access"
  ],
  "response_code": 401
}
```

---

## 4. Architectural Best Practices

1. **Strictly HTTP POST**: Never use HTTP `GET`. Even for data extraction (e.g. `/city-list`, `/thana-list`), sending a `GET` request will result in an HTTP 405 Method Not Allowed or 404 Route Not Found error.
2. **Empty JSON Body Handling**: When invoking list endpoints that don't require parameters (such as `/packages` or `/city-list`), send an empty JSON object `{}` or no body with `Content-Type: application/json`.
3. **Timeout Strategy**: Set network client timeouts to at least **30 seconds** for order bookings and label printing endpoints.
