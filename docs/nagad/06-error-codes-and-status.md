# 06. Error Codes, Status Mapping & Troubleshooting

A complete engineering guide to Nagad status codes, error response structures, common cryptographic pitfalls, and production debugging.

---

## 1. Gateway Error Response Structure

When a request fails validation or cryptographic verification, the Nagad API returns an error JSON object:

```json
{
  "status": "Failed",
  "reason": "1002",
  "message": "Decryption failed or invalid public key"
}
```

---

## 2. Status & Error Code Dictionary

| Code | Stage | Meaning | Typical Trigger | Resolution |
| :---: | :---: | :--- | :--- | :--- |
| `000` / `00_0000_000` | Terminal | **Success** | Transaction confirmed by core banking | Mark order as paid and credit balance |
| `1001` | Init | Invalid Merchant | Merchant ID does not exist or inactive | Verify `NAGAD_MERCHANT_ID` with KAM |
| `1002` | Crypto | Decryption Failed | Corrupted RSA key or wrong Nagad PG key | Ensure using official Nagad PG Public Key |
| `1003` | Crypto | Invalid Signature | Signature does not match private key | Check `SHA-256 with RSA` signing logic |
| `1004` | Network | IP Not Whitelisted | Request sent from unapproved IP | Whitelist server public IPv4 with Nagad |
| `1005` | Complete | Invalid Currency | Sent `"BDT"` or `"840"` instead of `"050"` | Set `currencyCode: "050"` |
| `1006` | Init | Clock Skew / Nonce | Server clock differs by > 5 minutes | Synchronize server time via NTP (`Asia/Dhaka`) |
| `1007` | Payment | Insufficient Balance | Customer does not have enough funds | Customer must add funds or try another method |
| `1008` | UI | Transaction Aborted | Customer clicked "Cancel" on gateway | Log cancellation; keep order in `Pending` |
| `1009` | Security | PIN/OTP Failed | Customer entered wrong OTP or PIN 3 times | Customer must wait 30 minutes to re-attempt |
| `999` | Upstream | Core Bank Timeout | Intermittent Nagad core bank outage | Retry payment verification after 60 seconds |

---

## 3. Top 4 Production Pitfalls & Fixes

### 1. PEM Key Formatting (`\r\n` vs `\n`)
- **Problem**: When storing RSA keys in `.env` files or database columns, Windows `\r\n` carriage returns can corrupt OpenSSL parsing.
- **Solution**: Strip all whitespace, newlines, and headers when storing in `.env`, and format programmatically:
  ```php
  $formattedKey = "-----BEGIN RSA PRIVATE KEY-----\n" 
      . chunk_split(trim($keyWithoutHeaders), 64, "\n") 
      . "-----END RSA PRIVATE KEY-----";
  ```

### 2. Currency Code `050` vs `"BDT"`
- **Problem**: Developers often pass `"BDT"` for currency, causing an immediate payment rejection.
- **Solution**: Nagad strictly enforces the ISO 4217 numeric currency code for Bangladeshi Taka, which is **`050`**.

### 3. Outbound NAT / Dynamic IP Whitelisting
- **Problem**: In cloud environments (AWS, DigitalOcean, Heroku), outbound API requests might exit through a NAT gateway or load balancer with a different IP than your server's inbound IP.
- **Solution**: Query `https://api.ipify.org` from your application runtime to confirm your outbound IPv4 address before submitting to Nagad.

### 4. Zero-Trust Verification Rule
- **Problem**: Updating order state directly from GET callback parameters (`?status=Success`).
- **Solution**: Treat callback parameters solely as a prompt to trigger the server-to-server `GET /verify/payment/{paymentRefId}` call.
