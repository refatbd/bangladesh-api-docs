/**
 * RedX Delivery OpenAPI v1.0.0-beta Node.js Client
 * 
 * Clean, modern async/await SDK for Node 18+ using native fetch.
 * 
 * @author Refat Rahman <https://refat.ovh>
 * @github https://github.com/refatbd
 * @license MIT
 */

class RedxClient {
  static SANDBOX_URL = 'https://sandbox.redx.com.bd/v1.0.0-beta';
  static PRODUCTION_URL = 'https://openapi.redx.com.bd/v1.0.0-beta';

  /**
   * @param {string} jwtToken RedX Merchant Bearer JWT token
   * @param {boolean} [isSandbox=false]
   */
  constructor(jwtToken, isSandbox = false) {
    if (!jwtToken) {
      throw new Error('RedX JWT token is required');
    }
    this.jwtToken = jwtToken.trim();
    this.baseUrl = isSandbox ? RedxClient.SANDBOX_URL : RedxClient.PRODUCTION_URL;
  }

  async _request(method, endpoint, body = null) {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = {
      'API-ACCESS-TOKEN': `Bearer ${this.jwtToken}`,
      'Accept': 'application/json',
    };

    const options = { method, headers };

    if (body && (method === 'POST' || method === 'PATCH')) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      const msg = data.message || data.error || `HTTP ${response.status}`;
      const err = new Error(`RedX API Error [${response.status}]: ${msg}`);
      err.status = response.status;
      err.details = data;
      throw err;
    }

    return data;
  }

  // Tracking
  async trackParcel(trackingId) {
    return this._request('GET', `/parcel/track/${encodeURIComponent(trackingId)}`);
  }

  // Parcel Info
  async getParcelInfo(trackingId) {
    return this._request('GET', `/parcel/info/${encodeURIComponent(trackingId)}`);
  }

  // Create Parcel
  async createParcel(parcelData) {
    return this._request('POST', '/parcel', parcelData);
  }

  // Update Parcel (Cancel, Address change, etc.)
  async updateParcel(trackingId, propertyName, newValue, reason = '') {
    return this._request('PATCH', '/parcels', {
      entity_type: 'parcel-tracking-id',
      entity_id: trackingId,
      update_details: {
        property_name: propertyName,
        new_value: newValue,
        reason: reason,
      }
    });
  }

  async cancelParcel(trackingId, reason = 'Merchant cancellation') {
    return this.updateParcel(trackingId, 'status', 'cancelled', reason);
  }

  // Areas
  async getAreas() {
    return this._request('GET', '/areas');
  }

  async getAreasByPostCode(postCode) {
    return this._request('GET', `/areas?post_code=${encodeURIComponent(postCode)}`);
  }

  async getAreasByDistrict(districtName) {
    return this._request('GET', `/areas?district_name=${encodeURIComponent(districtName)}`);
  }

  // Pickup Stores
  async createPickupStore(name, phone, address, areaId) {
    return this._request('POST', '/pickup/store', {
      name, phone, address, area_id: areaId
    });
  }

  async getPickupStores() {
    return this._request('GET', '/pickup/stores');
  }

  async getPickupStoreInfo(storeId) {
    return this._request('GET', `/pickup/store/info/${storeId}`);
  }

  // Charge Calculator
  async calculateCharge(deliveryAreaId, pickupAreaId, cashCollectionAmount, weight) {
    const params = new URLSearchParams({
      delivery_area_id: deliveryAreaId,
      pickup_area_id: pickupAreaId,
      cash_collection_amount: cashCollectionAmount,
      weight: weight,
    });
    return this._request('GET', `/charge/charge_calculator?${params.toString()}`);
  }
}

module.exports = RedxClient;
