/**
 * Pathao Courier Aladdin API v1 Node.js Client
 * 
 * Production async/await SDK with automated OAuth 2.0 token management.
 * 
 * @author Refat Rahman <https://refat.ovh>
 * @github https://github.com/refatbd
 * @license MIT
 */

class PathaoClient {
  static SANDBOX_URL = 'https://courier-api-sandbox.pathao.com';
  static PRODUCTION_URL = 'https://api-hermes.pathao.com';

  constructor({
    clientId,
    clientSecret,
    username,
    password,
    isSandbox = false,
    cachedAccessToken = null,
    cachedRefreshToken = null
  }) {
    this.clientId = clientId;
    this.clientSecret = clientSecret;
    this.username = username;
    this.password = password;
    this.baseUrl = isSandbox ? PathaoClient.SANDBOX_URL : PathaoClient.PRODUCTION_URL;
    this.accessToken = cachedAccessToken;
    this.refreshToken = cachedRefreshToken;
  }

  // Issue Access Token
  async issueToken() {
    const res = await this._rawRequest('POST', '/aladdin/api/v1/issue-token', {
      client_id: this.clientId,
      client_secret: this.clientSecret,
      grant_type: 'password',
      username: this.username,
      password: this.password,
    });

    if (res.access_token) {
      this.accessToken = res.access_token;
      this.refreshToken = res.refresh_token;
    }
    return res;
  }

  // Renew Token
  async renewToken() {
    if (!this.refreshToken) {
      return this.issueToken();
    }

    const res = await this._rawRequest('POST', '/aladdin/api/v1/issue-token', {
      client_id: this.clientId,
      client_secret: this.clientSecret,
      grant_type: 'refresh_token',
      refresh_token: this.refreshToken,
    });

    if (res.access_token) {
      this.accessToken = res.access_token;
      this.refreshToken = res.refresh_token || this.refreshToken;
    }
    return res;
  }

  async getAccessToken() {
    if (!this.accessToken) {
      await this.issueToken();
    }
    return this.accessToken;
  }

  // City, Zone & Area
  async getCities() {
    return this._authRequest('GET', '/aladdin/api/v1/city-list');
  }

  async getZones(cityId) {
    return this._authRequest('GET', `/aladdin/api/v1/cities/${cityId}/zone-list`);
  }

  async getAreas(zoneId) {
    return this._authRequest('GET', `/aladdin/api/v1/zones/${zoneId}/area-list`);
  }

  // Stores
  async createStore(storeData) {
    return this._authRequest('POST', '/aladdin/api/v1/stores', storeData);
  }

  async getStores() {
    return this._authRequest('GET', '/aladdin/api/v1/stores');
  }

  // Orders
  async createOrder(orderData) {
    return this._authRequest('POST', '/aladdin/api/v1/orders', orderData);
  }

  async createBulkOrders(orders) {
    return this._authRequest('POST', '/aladdin/api/v1/orders/bulk', { orders });
  }

  async getOrderInfo(consignmentId) {
    return this._authRequest('GET', `/aladdin/api/v1/orders/${encodeURIComponent(consignmentId)}/info`);
  }

  // Price Plan
  async calculatePrice(priceParams) {
    return this._authRequest('POST', '/aladdin/api/v1/merchant/price-plan', priceParams);
  }

  // Internal Request Handlers
  async _authRequest(method, endpoint, body = null) {
    let token = await this.getAccessToken();
    try {
      return await this._rawRequest(method, endpoint, body, { 'Authorization': `Bearer ${token}` });
    } catch (err) {
      if (err.status === 401) {
        await this.renewToken();
        token = await this.getAccessToken();
        return await this._rawRequest(method, endpoint, body, { 'Authorization': `Bearer ${token}` });
      }
      throw err;
    }
  }

  async _rawRequest(method, endpoint, body = null, extraHeaders = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json; charset=UTF-8',
      'Accept': 'application/json',
      ...extraHeaders,
    };

    const options = { method, headers };
    if (body && (method === 'POST' || method === 'PATCH')) {
      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const msg = data.message || data.error || `HTTP ${response.status}`;
      const err = new Error(`Pathao API Error [${response.status}]: ${msg}`);
      err.status = response.status;
      err.details = data;
      throw err;
    }

    return data;
  }
}

module.exports = PathaoClient;
