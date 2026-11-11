/**
 * Nagad Direct Online Payment Gateway (API v3.3) - Node.js SDK
 * Author: Refat Rahman (https://refat.ovh | @refatbd)
 * License: MIT
 */

const crypto = require('crypto');

class NagadClient {
  /**
   * @param {Object} config
   * @param {string} config.merchantId - Assigned Nagad Merchant ID
   * @param {string} config.merchantPrivateKey - 2048-bit RSA Private Key (PEM or base64)
   * @param {string} config.pgPublicKey - 2048-bit Nagad PG Public Key (PEM or base64)
   * @param {boolean} [config.sandbox=false] - Use sandbox environment
   * @param {string} [config.serverIp='127.0.0.1'] - Whitelisted server public IPv4
   * @param {number} [config.timeout=30000] - Request timeout in milliseconds
   */
  constructor({ merchantId, merchantPrivateKey, pgPublicKey, sandbox = false, serverIp = '127.0.0.1', timeout = 30000 }) {
    if (!merchantId || !merchantPrivateKey || !pgPublicKey) {
      throw new Error('NagadClient requires merchantId, merchantPrivateKey, and pgPublicKey.');
    }

    this.merchantId = merchantId.trim();
    this.merchantPrivateKey = NagadClient.formatPrivateKey(merchantPrivateKey);
    this.pgPublicKey = NagadClient.formatPublicKey(pgPublicKey);
    this.baseUrl = sandbox
      ? 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/'
      : 'https://api.mynagad.com/api/dfs/';
    this.serverIp = serverIp.trim();
    this.timeout = timeout;
  }

  // ==========================================
  // Core Payment Workflow Methods
  // ==========================================

  /**
   * Step 1: Initialize checkout with cryptographic challenge
   */
  async initializeCheckout(orderId, accountNumber = null) {
    if (!orderId) throw new Error('orderId is required.');

    const dateTime = NagadClient.formatDateTime(new Date());
    const challenge = NagadClient.generateChallenge(40);

    const sensitiveData = {
      merchantId: this.merchantId,
      datetime: dateTime,
      orderId,
      challenge
    };

    const sensitiveJson = JSON.stringify(sensitiveData);
    const encryptedSensitiveData = this.encryptWithPgPublicKey(sensitiveJson);
    const signature = this.generateSignature(sensitiveJson);

    const payload = {
      accountNumber,
      dateTime,
      sensitiveData: encryptedSensitiveData,
      signature
    };

    const url = `${this.baseUrl}check-out/initialize/${this.merchantId}/${orderId}`;
    const response = await this.httpPost(url, payload);

    if (response.reason || !response.sensitiveData) {
      const msg = response.message || 'Nagad Checkout Initialization Failed.';
      throw new Error(`Nagad Init Error: ${msg}`);
    }

    const decryptedJson = this.decryptWithPrivateKey(response.sensitiveData);
    let decryptedData = {};
    try {
      decryptedData = JSON.parse(decryptedJson);
    } catch (e) {
      throw new Error(`Failed to parse decrypted Nagad init response: ${decryptedJson}`);
    }

    if (!decryptedData.paymentReferenceId) {
      throw new Error('Nagad Init Error: Missing paymentReferenceId in response.');
    }

    // Anti-replay challenge verification
    if (decryptedData.challenge && decryptedData.challenge !== challenge) {
      throw new Error('Nagad Security Error: Challenge nonce mismatch.');
    }

    return {
      paymentReferenceId: decryptedData.paymentReferenceId,
      challenge: decryptedData.challenge || challenge
    };
  }

  /**
   * Step 2: Complete checkout and acquire hosted payment redirect URL
   */
  async completeCheckout(paymentReferenceId, orderId, amount, callbackUrl, challenge) {
    if (!paymentReferenceId || !orderId || !amount || !callbackUrl) {
      throw new Error('paymentReferenceId, orderId, amount, and callbackUrl are required.');
    }

    const sensitiveDataOrder = {
      merchantId: this.merchantId,
      orderId,
      currencyCode: '050', // ISO 4217 numeric code for BDT
      amount: Number(amount).toFixed(2),
      challenge
    };

    const sensitiveJson = JSON.stringify(sensitiveDataOrder);
    const encryptedSensitiveData = this.encryptWithPgPublicKey(sensitiveJson);
    const signature = this.generateSignature(sensitiveJson);

    const payload = {
      sensitiveData: encryptedSensitiveData,
      signature,
      merchantCallbackURL: callbackUrl
    };

    const url = `${this.baseUrl}check-out/complete/${paymentReferenceId}`;
    const response = await this.httpPost(url, payload);

    if (!response.status || response.status !== 'Success' || !response.callBackUrl) {
      const msg = response.message || JSON.stringify(response);
      throw new Error(`Nagad Checkout Complete Error: ${msg}`);
    }

    return response;
  }

  /**
   * High-level 1-step checkout helper: runs initialize + complete and returns URL
   */
  async createPayment(orderId, amount, callbackUrl, accountNumber = null) {
    const init = await this.initializeCheckout(orderId, accountNumber);
    const complete = await this.completeCheckout(
      init.paymentReferenceId,
      orderId,
      amount,
      callbackUrl,
      init.challenge
    );
    return complete.callBackUrl;
  }

  /**
   * Step 4: Verify payment status with Nagad core engine
   */
  async verifyPayment(paymentRefId) {
    if (!paymentRefId) throw new Error('paymentRefId is required.');
    const url = `${this.baseUrl}verify/payment/${paymentRefId}`;
    return this.httpGet(url);
  }

  /**
   * Helper to parse callback query parameters
   */
  static parseCallback(callbackInput) {
    if (typeof callbackInput === 'object') return callbackInput;
    const urlObj = new URL(callbackInput, 'http://localhost');
    const params = {};
    urlObj.searchParams.forEach((val, key) => {
      params[key] = val;
    });
    return params;
  }

  // ==========================================
  // Cryptography Helpers (RSA PKCS#1 v1.5)
  // ==========================================

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

  generateSignature(plainText) {
    const signer = crypto.createSign('RSA-SHA256');
    signer.update(plainText);
    signer.end();
    return signer.sign(this.merchantPrivateKey, 'base64');
  }

  decryptWithPrivateKey(base64CryptoText) {
    const buffer = Buffer.from(base64CryptoText, 'base64');
    const decrypted = crypto.privateDecrypt(
      {
        key: this.merchantPrivateKey,
        padding: crypto.constants.RSA_PKCS1_PADDING
      },
      buffer
    );
    return decrypted.toString('utf8');
  }

  static generateChallenge(length = 40) {
    return crypto.randomBytes(length / 2).toString('hex');
  }

  static formatDateTime(date) {
    // Format YYYYMMDDHHmmss
    const pad = n => String(n).padStart(2, '0');
    return (
      date.getFullYear() +
      pad(date.getMonth() + 1) +
      pad(date.getDate()) +
      pad(date.getHours()) +
      pad(date.getMinutes()) +
      pad(date.getSeconds())
    );
  }

  static formatPrivateKey(key) {
    const clean = key.replace(/-----.*?-----|\s+/g, '');
    const lines = clean.match(/.{1,64}/g) || [];
    return `-----BEGIN RSA PRIVATE KEY-----\n${lines.join('\n')}\n-----END RSA PRIVATE KEY-----`;
  }

  static formatPublicKey(key) {
    const clean = key.replace(/-----.*?-----|\s+/g, '');
    const lines = clean.match(/.{1,64}/g) || [];
    return `-----BEGIN PUBLIC KEY-----\n${lines.join('\n')}\n-----END PUBLIC KEY-----`;
  }

  // ==========================================
  // HTTP Engine (Native fetch)
  // ==========================================

  async httpPost(url, payload) {
    const headers = {
      'Content-Type': 'application/json',
      'X-KM-Api-Version': 'v-0.2.0',
      'X-KM-IP-V4': this.serverIp,
      'X-KM-Client-Type': 'PC_WEB',
      Accept: 'application/json'
    };

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), this.timeout);

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(payload),
        signal: controller.signal
      });

      const text = await res.text();
      let data = {};
      try {
        data = text ? JSON.parse(text) : {};
      } catch (e) {
        throw new Error(`Invalid JSON from Nagad (HTTP ${res.status}): ${text}`);
      }
      return data;
    } finally {
      clearTimeout(timer);
    }
  }

  async httpGet(url) {
    const headers = {
      'X-KM-Api-Version': 'v-0.2.0',
      'X-KM-IP-V4': this.serverIp,
      'X-KM-Client-Type': 'PC_WEB',
      Accept: 'application/json'
    };

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), this.timeout);

    try {
      const res = await fetch(url, {
        method: 'GET',
        headers,
        signal: controller.signal
      });

      const text = await res.text();
      let data = {};
      try {
        data = text ? JSON.parse(text) : {};
      } catch (e) {
        throw new Error(`Invalid JSON from Nagad Verify (HTTP ${res.status}): ${text}`);
      }
      return data;
    } finally {
      clearTimeout(timer);
    }
  }
}

module.exports = { NagadClient };
