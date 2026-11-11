# 01. Getting Started & Authentication

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 🌐 Production API Gateway Base URL

```text
https://portal.packzy.com/api/v1
```

> [!IMPORTANT]
> **Domain Clarification**: Steadfast Courier's official API Gateway runs on `portal.packzy.com`. The older domain `portal.steadfast.com.bd` is deprecated and lacks DNS records. Always use `https://portal.packzy.com/api/v1` for all production requests.

---

## 🔑 Authentication Headers

Steadfast Courier uses **dual static API header authentication**. There is no session login token, OAuth bearer, or token refresh flow required. You must send both credentials on **every HTTP request**:

| Header Name | Type | Description |
| :--- | :--- | :--- |
| `Api-Key` | String | Your merchant account API Key issued from Steadfast dashboard. |
| `Secret-Key` | String | Your merchant account Secret Key issued from Steadfast dashboard. |
| `Content-Type` | String | Must be set to `application/json` for all `POST` requests. |
| `Accept` | String | `application/json` |

### Where to Obtain Your Keys
1. Log in to your Steadfast Merchant Dashboard at [steadfast.com.bd](https://steadfast.com.bd).
2. Navigate to **Tools -> API -> API Guide** or **Your API Keys**.
3. Copy your profile's `Api-Key` and `Secret-Key`.

---

## 🚦 Rate Limits & Lockout Defense

| Policy | Threshold | Consequence of Breach |
| :--- | :--- | :--- |
| **General Query Limit** | 1,000 requests / minute | `HTTP 429 Too Many Requests` |
| **Parcel Booking Limit** | 6,000 requests / minute | `HTTP 429 Too Many Requests` |
| **Authentication Failures** | 10 failures in 5 minutes | **60-Minute Complete IP & Key Ban** (Even with valid keys!) |

> [!CAUTION]
> **Do NOT Retry 401 In A Loop!**  
> If an API call fails with `HTTP 401 Unauthorized`, stop your script immediately. Steadfast's firewall counts failed attempts: after **10 bad attempts within 5 minutes**, your IP and API key are locked out for **60 minutes**. Validate your credentials before retrying.

---

## ⚠️ Two Critical Implementation Quirks

### 1. Automatic Character Stripping
Before Steadfast stores an order into its database, its gateway strips the following 6 characters and replaces each with a space:
```text
{   }   ;   <   >   $
```
If you send: `"note": "Total $500 <urgent>; don't {break}"`  
Steadfast saves: `"note": "Total  500  urgent   don't  break "`

### 2. Length Truncation (Not Validation Rejection)
Steadfast **truncates** over-long strings rather than rejecting the payload:

| Field Name | Max Character Length | Truncation Behavior |
| :--- | :--- | :--- |
| `invoice` | **100 characters** | Characters past 100 are automatically dropped |
| `recipient_name` | **100 characters** | Characters past 100 are automatically dropped |
| `recipient_phone` | **40 characters** | Characters past 40 are automatically dropped |
| `recipient_address` | **490 characters** | Characters past 490 are automatically dropped |
| `note` | **480 characters** | Characters past 480 are automatically dropped |

---

## 🩺 Service Health Check (`GET /ping`)

The `/ping` endpoint requires **no API keys**. Always use this in your system health check or diagnostic script to verify that your server can reach the Steadfast API gateway.

### Endpoint
```http
GET https://portal.packzy.com/api/v1/ping
```

### cURL Example
```bash
curl -X GET https://portal.packzy.com/api/v1/ping
```

### Response (`HTTP 200 OK`)
```json
{
  "status": 200,
  "message": "pong",
  "service": "steadfast-api",
  "version": "v1",
  "time": "2026-10-27T10:48:27+06:00"
}
```
