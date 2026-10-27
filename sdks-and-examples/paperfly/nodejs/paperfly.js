/**
 * Paperfly Courier API v2 Node.js Client
 * 
 * Modern async/await SDK using native fetch with Basic Auth and paperflykey header.
 * 
 * @author Refat Rahman <https://refat.ovh>
 * @github https://github.com/refatbd
 * @license MIT
 */

class PaperflyClient {
  static BASE_URL = 'https://api.paperfly.com.bd';
  static DEFAULT_KEY = 'Paperfly_~La?Rj73FcLm';

  constructor({
    username,
    password,
    paperflyKey = null,
    baseUrl = null
  }) {
    if (!username || !password) {
      throw new Error('Paperfly username and password are required');
    }
    this.username = username.trim();
    this.password = password.trim();
    this.paperflyKey = paperflyKey || PaperflyClient.DEFAULT_KEY;
    this.baseUrl = (baseUrl || PaperflyClient.BASE_URL).replace(/\/$/, '');
    this.basicAuth = Buffer.from(`${this.username}:${this.password}`).toString('base64');
  }

  // Create Standard Order
  async createOrder(orderData) {
    return this._request('POST', '/merchant/api/service/new_order_v2.php', orderData);
  }

  // Create Exchange Order
  async createExchangeOrder(orderData) {
    return this._request('POST', '/merchant/api/service/new_order_v2.php', {
      ...orderData,
      orderType: 'Exchange'
    });
  }

  // Track Order
  async trackOrder(referenceNumber) {
    return this._request('POST', '/API-Order-Tracking', {
      ReferenceNumber: referenceNumber
    });
  }

  // Cancel Order
  async cancelOrder(orderId) {
    return this._request('POST', '/api/v1/cancel-order', {
      order_id: orderId
    });
  }

  // Internal Request Handler
  async _request(method, endpoint, body = null) {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Basic ${this.basicAuth}`,
      'paperflykey': this.paperflyKey,
    };

    const options = {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
    };

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const msg = data.message || data.error || `HTTP ${response.status}`;
      const err = new Error(`Paperfly API Error [${response.status}]: ${msg}`);
      err.status = response.status;
      err.details = data;
      throw err;
    }

    return data;
  }
}

module.exports = PaperflyClient;
