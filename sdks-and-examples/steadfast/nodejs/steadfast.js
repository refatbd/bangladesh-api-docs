/**
 * Modern Node.js / JavaScript Client for Steadfast Courier API (v1)
 *
 * Authored & Maintained by Refat Rahman (https://github.com/refatbd)
 * Website: https://refat.ovh
 *
 * @license MIT
 */

export class SteadfastClient {
  constructor({ apiKey, secretKey, baseUrl = 'https://portal.packzy.com/api/v1', timeout = 20000 }) {
    if (!apiKey || !secretKey) {
      throw new Error('SteadfastClient requires both apiKey and secretKey');
    }
    this.apiKey = apiKey.trim();
    this.secretKey = secretKey.trim();
    this.baseUrl = baseUrl.replace(/\/+$/, '');
    this.timeout = timeout;
  }

  /**
   * Diagnostic health check ping (requires no API keys)
   */
  async ping() {
    return this._request('GET', '/ping', null, false);
  }

  /**
   * Get current merchant COD wallet balance
   */
  async getBalance() {
    return this._request('GET', '/get_balance');
  }

  /**
   * Book a single parcel with character sanitization and limits
   */
  async createOrder({ invoice, recipient_name, recipient_phone, recipient_address, cod_amount, note = '' }) {
    const sanitize = (text, max) => {
      if (!text) return '';
      return String(text).replace(/[{}\;<>\\$]/g, ' ').trim().slice(0, max);
    };

    const payload = {
      invoice: sanitize(invoice || `INV-${Date.now()}`, 100),
      recipient_name: sanitize(recipient_name || 'Customer', 100),
      recipient_phone: sanitize(recipient_phone, 40),
      recipient_address: sanitize(recipient_address, 490),
      cod_amount: Math.max(0, Number(cod_amount) || 0),
      note: sanitize(note, 480),
    };

    return this._request('POST', '/create_order', payload);
  }

  /**
   * Bulk book up to 500 parcels
   */
  async createBulkOrder(orders, extended = false) {
    const endpoint = extended ? '/create_order/bulk-order/extended' : '/create_order/bulk-order';
    return this._request('POST', endpoint, { data: orders });
  }

  /**
   * Query status by Steadfast Consignment ID
   */
  async getStatusByCid(consignmentId) {
    return this._request('GET', `/status_by_cid/${consignmentId}`);
  }

  /**
   * Query status with granular return progress
   */
  async getStatusWithReturn(consignmentId) {
    return this._request('GET', `/status_with_return_status_by_cid/${consignmentId}`);
  }

  /**
   * Query status by internal merchant invoice number
   */
  async getStatusByInvoice(invoice) {
    return this._request('GET', `/status_by_invoice/${encodeURIComponent(invoice)}`);
  }

  /**
   * Query status by public tracking code
   */
  async getStatusByTrackingCode(trackingCode) {
    return this._request('GET', `/status_by_trackingcode/${encodeURIComponent(trackingCode)}`);
  }

  /**
   * Get chronological event timeline history
   */
  async getTrackingTimeline(invoice) {
    return this._request('GET', `/trackings_by_invoice/${encodeURIComponent(invoice)}`);
  }

  /**
   * Request rider collection
   */
  async createPickupRequest(params) {
    return this._request('POST', '/create_pickup_request', params);
  }

  /**
   * Request pre-delivery parcel recall
   */
  async createReturnRequest(consignmentId, reason) {
    return this._request('POST', '/create_return_request', {
      consignment_id: consignmentId,
      reason,
    });
  }

  /**
   * Get return requests list (10 per page)
   */
  async getReturnRequests(page = 1) {
    return this._request('GET', `/get_return_requests?page=${page}`);
  }

  /**
   * Get payouts history (10 per page)
   */
  async getPayments(page = 1) {
    return this._request('GET', `/payments?page=${page}`);
  }

  /**
   * Get detailed payment settlement
   */
  async getPaymentDetails(paymentId) {
    return this._request('GET', `/payments/${paymentId}`);
  }

  /**
   * List covered police stations / thanas
   */
  async getPoliceStations() {
    return this._request('GET', '/police_stations');
  }

  /**
   * Cross-merchant customer fraud score & delivery history
   */
  async checkFraudScore(phone) {
    const cleanPhone = String(phone).replace(/\D/g, '');
    return this._request('GET', `/fraud_check/score/${cleanPhone}`);
  }

  /**
   * Internal HTTP execution
   */
  async _request(method, path, body = null, withAuth = true) {
    const url = `${this.baseUrl}${path}`;
    const headers = {
      'Accept': 'application/json',
    };

    if (withAuth) {
      headers['Api-Key'] = this.apiKey;
      headers['Secret-Key'] = this.secretKey;
    }

    const options = {
      method,
      headers,
    };

    if (body && ['POST', 'PUT', 'PATCH'].includes(method.toUpperCase())) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);
    options.signal = controller.signal;

    try {
      const response = await fetch(url, options);
      clearTimeout(timeoutId);

      const data = await response.json().catch(() => ({}));
      return {
        success: response.ok,
        status: response.status,
        data,
      };
    } catch (error) {
      clearTimeout(timeoutId);
      return {
        success: false,
        status: 0,
        message: error.name === 'AbortError' ? 'Request timed out' : error.message,
      };
    }
  }
}

export default SteadfastClient;
