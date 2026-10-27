/**
 * eCourier Courier API v5.5 - Node.js SDK
 * Author: Refat Rahman (https://refat.ovh | @refatbd)
 * License: MIT
 */

class EcourierClient {
  /**
   * @param {Object} config
   * @param {string} config.apiKey - eCourier API Key
   * @param {string} config.apiSecret - eCourier API Secret
   * @param {string} config.userId - eCourier User ID
   * @param {boolean} [config.sandbox=false] - Use staging environment
   * @param {number} [config.timeout=30000] - Request timeout in milliseconds
   */
  constructor({ apiKey, apiSecret, userId, sandbox = false, timeout = 30000 }) {
    if (!apiKey || !apiSecret || !userId) {
      throw new Error('EcourierClient requires apiKey, apiSecret, and userId.');
    }

    this.apiKey = apiKey.trim();
    this.apiSecret = apiSecret.trim();
    this.userId = userId.trim();
    this.baseUrl = sandbox
      ? 'https://staging.ecourier.com.bd/api'
      : 'https://backoffice.ecourier.com.bd/api';
    this.timeout = timeout;
  }

  // ==========================================
  // 1. Geographic & Coverage Endpoints
  // ==========================================

  async getCities() {
    return this.request('/city-list');
  }

  async getThanas(city) {
    if (!city) throw new Error('city parameter is required.');
    return this.request('/thana-list', { city });
  }

  async getAreasByPostcode(postcode) {
    if (!postcode) throw new Error('postcode parameter is required.');
    return this.request('/area-list', { postcode });
  }

  async getPostcodes(city, thana) {
    if (!city || !thana) throw new Error('city and thana parameters are required.');
    return this.request('/postcode-list', { city, thana });
  }

  async getAreasByDistrict(district) {
    if (!district) throw new Error('district parameter is required.');
    return this.request('/area-by-district', { district });
  }

  async getBranches() {
    return this.request('/branch-list');
  }

  async getHubInfoByArea(area) {
    if (!area) throw new Error('area parameter is required.');
    return this.request('/hub-info-by-area', { area });
  }

  // ==========================================
  // 2. Package Management Endpoints
  // ==========================================

  async getPackages() {
    return this.request('/packages');
  }

  // ==========================================
  // 3. Order Placement & Reseller Endpoints
  // ==========================================

  async placeOrder(orderData) {
    return this.request('/order-place', orderData);
  }

  async placeResellerOrder(resellerData) {
    return this.request('/order-place-reseller', resellerData);
  }

  async cancelOrder(tracking, comment = 'Merchant requested cancellation') {
    if (!tracking) throw new Error('tracking ID is required.');
    return this.request('/cancel-order', { tracking, comment });
  }

  async cancelChildOrder(tracking, comment = 'Child partner cancelled') {
    if (!tracking) throw new Error('tracking ID is required.');
    return this.request('/cancel-order-child', { tracking, comment });
  }

  // ==========================================
  // 4. Milestone Tracking & Invoice Labels
  // ==========================================

  async track({ ecr, product_id }) {
    const payload = {};
    if (ecr) payload.ecr = ecr;
    if (product_id) payload.product_id = product_id;
    return this.request('/track', payload);
  }

  async trackByEcr(ecr) {
    return this.track({ ecr });
  }

  async trackChild(ecr) {
    if (!ecr) throw new Error('ecr tracking ID is required.');
    return this.request('/track-child', { ecr });
  }

  async getLabelPrintUrl(tracking) {
    if (!tracking) throw new Error('tracking ID is required.');
    return this.request('/label-print', { tracking });
  }

  async getPaymentStatus(tracking) {
    if (!tracking) throw new Error('tracking ID is required.');
    return this.request('/payment-status', { tracking });
  }

  // ==========================================
  // 5. Fraud Alert & Booster VAS Services
  // ==========================================

  async checkFraudStatus(phoneNumber) {
    if (!phoneNumber) throw new Error('phoneNumber is required.');
    return this.request('/fraud-status-check', { number: phoneNumber });
  }

  async sendBoosterSms({ number, op_title = 'booster', text_type = 'text', op_body, service_type = 'mask' }) {
    if (!number || !op_body) throw new Error('number and op_body are required for booster SMS.');
    return this.request('/bs-sms', {
      number: Array.isArray(number) ? number : [number],
      op_title,
      text_type,
      op_body,
      service_type
    });
  }

  async sendBoosterTopUp(number, amount, operatorType = 1) {
    if (!number || !amount) throw new Error('number and amount are required for top-up.');
    return this.request('/bs-top', {
      number,
      amount,
      operator_type: operatorType
    });
  }

  async getBoosterTopUpStatus(transactionId) {
    if (!transactionId) throw new Error('transactionId is required.');
    return this.request('/bs-top-status', { transaction_id: transactionId });
  }

  async sendBoosterOtp(number, otpBody, serviceType = 'mask') {
    if (!number || !otpBody) throw new Error('number and otpBody are required for OTP.');
    return this.request('/bs-top-otp', {
      number,
      op_title: 'booster',
      text_type: 'text',
      op_body: otpBody,
      service_type: serviceType
    });
  }

  // ==========================================
  // Core HTTP Engine (Native fetch)
  // ==========================================

  async request(path, body = {}) {
    const url = `${this.baseUrl.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;

    const headers = {
      'API-KEY': this.apiKey,
      'API-SECRET': this.apiSecret,
      'USER-ID': this.userId,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), this.timeout);

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(body),
        signal: controller.signal
      });

      const text = await response.text();
      let data = {};
      try {
        data = text ? JSON.parse(text) : {};
      } catch (err) {
        throw new Error(`Failed to parse JSON response from eCourier (HTTP ${response.status}): ${text}`);
      }

      if (!response.ok) {
        let errorMsg = `eCourier API Error (${response.status})`;
        if (data.errors && Array.isArray(data.errors)) {
          errorMsg += ' - ' + data.errors.filter(Boolean).join('; ');
        } else if (data.message) {
          errorMsg += ' - ' + (typeof data.message === 'object' ? JSON.stringify(data.message) : data.message);
        }
        const error = new Error(errorMsg);
        error.status = response.status;
        error.data = data;
        throw error;
      }

      return data;
    } catch (err) {
      if (err.name === 'AbortError') {
        throw new Error(`eCourier API request timed out after ${this.timeout}ms`);
      }
      throw err;
    } finally {
      clearTimeout(timer);
    }
  }
}

module.exports = { EcourierClient };
