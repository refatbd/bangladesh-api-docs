# 02. Cryptography, Signatures & Key Operations

Nagad does not transmit plain JSON payloads over the network. Instead, sensitive parameters are encrypted using asymmetric RSA public-key cryptography, and verifiable cryptographic signatures are computed using SHA-256 with RSA.

---

## 1. Cryptographic Primitive Requirements

| Operation | Algorithm | Padding Scheme | Key Used |
| :--- | :--- | :--- | :--- |
| **Payload Encryption** | `RSA-2048` | `PKCS#1 v1.5` | **Nagad PG Public Key** |
| **Signature Generation** | `SHA-256 with RSA` | `PKCS#1 v1.5` | **Merchant Private Key** |
| **Response Decryption** | `RSA-2048` | `PKCS#1 v1.5` | **Merchant Private Key** |
| **Signature Verification** | `SHA-256 with RSA` | `PKCS#1 v1.5` | **Nagad PG Public Key** |

---

## 2. Multi-Language Implementations

### A. PHP 8.x (OpenSSL Extension)

```php
<?php

class NagadCrypto
{
    private string $merchantPrivateKey;
    private string $pgPublicKey;

    public function __construct(string $merchantPrivateKeyPem, string $pgPublicKeyPem)
    {
        $this->merchantPrivateKey = $merchantPrivateKeyPem;
        $this->pgPublicKey = $pgPublicKeyPem;
    }

    /**
     * 1. Encrypt plaintext JSON with Nagad PG Public Key
     */
    public function encryptWithPgPublicKey(string $plainText): string
    {
        $formattedKey = "-----BEGIN PUBLIC KEY-----\n" . trim($this->pgPublicKey) . "\n-----END PUBLIC KEY-----";
        $keyResource = openssl_pkey_get_public($formattedKey);
        
        if (!$keyResource) {
            throw new RuntimeException("Invalid Nagad PG Public Key format.");
        }

        $cryptoText = '';
        $success = openssl_public_encrypt($plainText, $cryptoText, $keyResource, OPENSSL_PKCS1_PADDING);
        if (!$success) {
            throw new RuntimeException("RSA public encryption failed.");
        }

        return base64_encode($cryptoText);
    }

    /**
     * 2. Sign plaintext JSON with Merchant Private Key
     */
    public function generateSignature(string $plainText): string
    {
        $formattedKey = "-----BEGIN RSA PRIVATE KEY-----\n" . trim($this->merchantPrivateKey) . "\n-----END RSA PRIVATE KEY-----";
        $keyResource = openssl_pkey_get_private($formattedKey);

        if (!$keyResource) {
            throw new RuntimeException("Invalid Merchant Private Key format.");
        }

        $signature = '';
        $success = openssl_sign($plainText, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        if (!$success) {
            throw new RuntimeException("RSA signature generation failed.");
        }

        return base64_encode($signature);
    }

    /**
     * 3. Decrypt response payload using Merchant Private Key
     */
    public function decryptWithPrivateKey(string $base64CryptoText): string
    {
        $formattedKey = "-----BEGIN RSA PRIVATE KEY-----\n" . trim($this->merchantPrivateKey) . "\n-----END RSA PRIVATE KEY-----";
        $keyResource = openssl_pkey_get_private($formattedKey);

        $plainText = '';
        $success = openssl_private_decrypt(base64_decode($base64CryptoText), $plainText, $keyResource, OPENSSL_PKCS1_PADDING);
        if (!$success) {
            throw new RuntimeException("RSA private decryption failed.");
        }

        return $plainText;
    }

    /**
     * 4. Generate random 40-character challenge string
     */
    public static function generateChallenge(int $length = 40): string
    {
        return bin2hex(random_bytes($length / 2));
    }
}
```

---

### B. Node.js (Native `crypto` Module)

```javascript
const crypto = require('crypto');

class NagadCryptoNode {
  constructor(merchantPrivateKeyPem, pgPublicKeyPem) {
    this.privateKey = `-----BEGIN RSA PRIVATE KEY-----\n${merchantPrivateKeyPem.trim()}\n-----END RSA PRIVATE KEY-----`;
    this.pgPublicKey = `-----BEGIN PUBLIC KEY-----\n${pgPublicKeyPem.trim()}\n-----END PUBLIC KEY-----`;
  }

  // 1. Encrypt using Nagad Public Key
  encryptWithPgPublicKey(plainText) {
    const buffer = Buffer.from(plainText, 'utf8');
    const encrypted = crypto.publicEncrypt(
      {
        key: this.pgPublicKey,
        padding: crypto.constants.RSA_PKCS1_PADDING
      },
      buffer
    );
    return encrypted.toString('base64');
  }

  // 2. Generate RSA-SHA256 Signature
  generateSignature(plainText) {
    const signer = crypto.createSign('RSA-SHA256');
    signer.update(plainText);
    signer.end();
    return signer.sign(this.privateKey, 'base64');
  }

  // 3. Decrypt using Merchant Private Key
  decryptWithPrivateKey(base64CryptoText) {
    const buffer = Buffer.from(base64CryptoText, 'base64');
    const decrypted = crypto.privateDecrypt(
      {
        key: this.privateKey,
        padding: crypto.constants.RSA_PKCS1_PADDING
      },
      buffer
    );
    return decrypted.toString('utf8');
  }

  static generateChallenge(length = 40) {
    return crypto.randomBytes(length / 2).toString('hex');
  }
}

module.exports = { NagadCryptoNode };
```

---

### C. Python 3.9+ (`cryptography` Library)

```python
import base64
import os
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.primitives.serialization import load_pem_private_key, load_pem_public_key


class NagadCryptoPython:
    def __init__(self, merchant_private_key_pem: str, pg_public_key_pem: str):
        priv_pem = f"-----BEGIN RSA PRIVATE KEY-----\n{merchant_private_key_pem.strip()}\n-----END RSA PRIVATE KEY-----".encode()
        pub_pem = f"-----BEGIN PUBLIC KEY-----\n{pg_public_key_pem.strip()}\n-----END PUBLIC KEY-----".encode()
        self.private_key = load_pem_private_key(priv_pem, password=None)
        self.pg_public_key = load_pem_public_key(pub_pem)

    def encrypt_with_pg_public_key(self, plain_text: str) -> str:
        encrypted = self.pg_public_key.encrypt(
            plain_text.encode("utf-8"),
            padding.PKCS1v15()
        )
        return base64.b64encode(encrypted).decode("utf-8")

    def generate_signature(self, plain_text: str) -> str:
        signature = self.private_key.sign(
            plain_text.encode("utf-8"),
            padding.PKCS1v15(),
            hashes.SHA256()
        )
        return base64.b64encode(signature).decode("utf-8")

    def decrypt_with_private_key(self, base64_crypto_text: str) -> str:
        raw_cipher = base64.b64decode(base64_crypto_text)
        decrypted = self.private_key.decrypt(
            raw_cipher,
            padding.PKCS1v15()
        )
        return decrypted.decode("utf-8")

    @staticmethod
    def generate_challenge(length: int = 40) -> str:
        return os.urandom(length // 2).hex()
```
