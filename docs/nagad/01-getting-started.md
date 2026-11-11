# 01. Getting Started & Merchant Onboarding

Integrating with the Nagad Online Payment Gateway requires signing a merchant service agreement with Nagad (Third Wave Technologies Ltd.), submitting security parameters, and establishing an asymmetric cryptographic key pair.

---

## 1. Onboarding Checklist

Before writing code or initiating sandbox tests, complete the following prerequisites with your Nagad Key Account Manager (KAM):

1. **Merchant Contract**: Execute the commercial online payment agreement.
2. **IP Whitelisting**: Provide your production and staging server public static IPv4 addresses. Outbound requests from non-whitelisted IPs will fail or be rejected.
3. **Callback URLs**: Submit your merchant redirect URLs (e.g., `https://yoursite.com/payment/nagad/callback`).
4. **Key Exchange**: Generate an RSA 2048-bit key pair. You provide your **Public Key** to Nagad, and Nagad provides you with their **PG Public Key**.

---

## 2. Generating Your RSA 2048-bit Key Pair

Nagad strictly requires **2048-bit RSA keys** formatted with standard PEM headers.

### Step 1: Generate Private Key
Run the following OpenSSL command on your server terminal:
```bash
openssl genrsa -out merchant_private_key.pem 2048
```

### Step 2: Extract Corresponding Public Key
Export the public key from the newly created private key:
```bash
openssl rsa -in merchant_private_key.pem -pubout -out merchant_public_key.pem
```

### Key Formats
- **Merchant Private Key (`merchant_private_key.pem`)**:
  ```text
  -----BEGIN RSA PRIVATE KEY-----
  MIIEowIBAAKCAQEA...
  ...
  -----END RSA PRIVATE KEY-----
  ```
- **Merchant Public Key (`merchant_public_key.pem`)**:
  ```text
  -----BEGIN PUBLIC KEY-----
  MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA...
  ...
  -----END PUBLIC KEY-----
  ```

> [!CAUTION]
> **Keep Your Private Key Confidential**: Never share `merchant_private_key.pem` with Nagad, third parties, or commit it to public git repositories. Only send `merchant_public_key.pem` to the Nagad integration team.

---

## 3. Environment Variables Configuration

Store credentials securely in your `.env` or secrets vault:

```env
# Nagad Gateway Environment (development | production)
NAGAD_APP_ENV=development

# Merchant Identifier (Assigned by Nagad)
NAGAD_MERCHANT_ID=6800000025

# Base64 body of your RSA 2048-bit Private Key (without headers or newlines)
NAGAD_MERCHANT_PRIVATE_KEY=MIIEowIBAAKCAQEA0dY8xL9p...

# Base64 body of Nagad PG Public Key (provided by Nagad)
NAGAD_PG_PUBLIC_KEY=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A...

# Server Public IPv4 (Must match whitelisted IP)
NAGAD_SERVER_IP=103.145.118.24

# Timezone (Strictly Asia/Dhaka)
NAGAD_TIMEZONE=Asia/Dhaka
```

---

## 4. Time Synchronization & Clock Drift

Nagad's API compares the `datetime` attribute inside encrypted payloads against its database cluster. If your server clock differs by **more than 5 minutes**, the request will be rejected with an authentication failure.

Ensure your server runs `chrony` or `systemd-timesyncd`:
```bash
sudo timedatectl set-ntp on
sudo timedatectl set-timezone Asia/Dhaka
```
