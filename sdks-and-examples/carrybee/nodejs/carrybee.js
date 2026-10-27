/**
 * Carrybee Delivery API v2.0 - Node.js SDK
 * Author: Refat Rahman (https://refat.ovh | @refatbd)
 * License: MIT
 */

const crypto = require('crypto');

class CarrybeeClient {
  /**
   * @param {Object} config
   * @param {string} config.clientId - Carrybee Client ID
   * @param {string} config.clientSecret - Carrybee Client Secret
   * @param {string} config.clientContext - Carrybee Client Context
   * @param {boolean} [config.sandbox=false] - Use sandbox environment
   * @param {number} [config.timeout=30000] - Request timeout in milliseconds
   */
  constructor({ clientId, clientSecret, clientContext, sandbox = false, timeout = 30000 }) {
    if (!clientId || !clientSecret || !clientContext) {
      throw new Error('CarrybeeClient requires clientId, clientSecret, and clientContext.');
    }

    this.clientId = clientId.trim();
    this.clientSecret = clientSecret.trim();
    this.clientContext = clientContext.trim();
    this.baseUrl = sandbox
      ? 'https://sandbox.carrybee.com'
      : 'https://developers.carrybee.com';
    this.timeout = timeout;
  }

  // ==========================================
  // Location & Coverage Endpoints
  // ==========================================

  async getCities() {
    return this.request('GET', '/api/v2/cities');
  }

  async getZones(cityId) {
    if (!cityId) throw new Error('cityId is required.');
    return this.request('GET', `/api/v2/zones/${cityId}`);
  }

  async getAreaSuggestion(search) {
    return this.request('GET', '/api/v2/area-suggestion', null, { search });
  }

  async resolveAddressDetails(address) {
    return this.request('POST', '/api/v2/address-details', { address });
  }

  // ==========================================
  // Store / Warehouse Endpoints
  // ==========================================

  async getStores() {
    return this.request('GET', '/api/v2/stores');
  }

  async createStore(storeData) {
    return this.request('POST', '/api/v2/stores', storeData);
  }

  // ==========================================
  // Order Management Endpoints
  // ==========================================

  async createOrder(orderData) {
    return this.request('POST', '/api/v2/orders', orderData);
  }

  async createBulkOrders(orders) {
    return this.request('POST', '/api/v2/orders-bulk', { orders });
  }

  async trackOrder(consignmentId) {
    if (!consignmentId) throw new Error('consignmentId is required.');
    return this.request('GET', `/api/v2/orders/track/${consignmentId}`);
  }

  async cancelOrder(consignmentId, reason = 'Merchant requested cancellation') {
    if (!consignmentId) throw new Error('consignmentId is required.');
    return this.request('POST', '/api/v2/orders/cancel', {
      consignment_id: consignmentId,
      reason
    });
  }

  // ==========================================
  // Reverse Pickup & Exchange Endpoints
  // ==========================================

  async createReversePickup(pickupData) {
    return this.request('POST', '/api/v2/orders/reverse-pickup', pickupData);
  }

  async createExchangeOrder(consignmentId, exchangeData) {
    if (!consignmentId) throw new Error('consignmentId is required.');
    return this.request('POST', `/api/v2/orders/${consignmentId}/exchange`, exchangeData);
  }

  // ==========================================
  // Webhook Handshake Verification Helper
  // ==========================================

  static verifyWebhookHandshake(incomingHeader, configuredSecret) {
    if (!incomingHeader || !configuredSecret) return false;
    const bufA = Buffer.from(incomingHeader);
    const bufB = Buffer.from(configuredSecret);
    if (bufA.length !== bufB.length) return false;
    return crypto.timingSafeEqual(bufA, bufB);
  }

  // ==========================================
  // Core HTTP Engine (Native fetch)
  // ==========================================

  async request(method, path, body = null, queryParams = {}) {
    const url = new URL(path, this.baseUrl);
    Object.keys(queryParams).forEach(key => {
      if (queryParams[key] !== undefined && queryParams[key] !== null) {
        url.searchParams.append(key, queryParams[key]);
      }
    });

    const headers = {
      'Client-ID': this.clientId,
      'Client-Secret': this.clientSecret,
      'Client-Context': this.clientContext,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    };

    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), this.timeout);

    const options = {
      method: method.toUpperCase(),
      headers,
      signal: controller.signal
    };

    if (body && ['POST', 'PUT', 'PATCH'].includes(options.method)) {
      options.body = JSON.stringify(body);
    }

    try {
      const response = await fetch(url.toString(), options);
      const text = await response.text();
      let data = {};
      try {
        data = text ? JSON.parse(text) : {};
      } catch (err) {
        throw new Error(`Failed to parse JSON response (HTTP ${response.status}): ${text}`);
      }

      if (!response.ok) {
        const errorMsg = data.message || `Carrybee API Error (${response.status})`;
        const error = new Error(errorMsg);
        error.status = response.status;
        error.data = data;
        throw error;
      }

      return data;
    } catch (err) {
      if (err.name === 'AbortError') {
        throw new Error(`Carrybee API request timed out after ${this.timeout}ms`);
      }
      throw err;
    } finally {
      clearTimeout(timer);
    }
  }
}

module.exports = { CarrybeeClient };
