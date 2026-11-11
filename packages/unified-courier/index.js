/**
 * Unified Bangladesh Courier Engine (@refatbd/bd-courier)
 * Maintained by Refat Rahman (https://refat.ovh, @refatbd)
 */

export const CourierStatus = {
  PENDING: 'pending',
  IN_TRANSIT: 'in_transit',
  DELIVERED: 'delivered',
  CANCELLED: 'cancelled',
  RETURNED: 'returned',
  UNKNOWN: 'unknown',

  normalize(provider, rawStatus = '') {
    const raw = String(rawStatus).toLowerCase().trim();
    if (['delivered', 'delivered_paid', 'delivered_partial'].includes(raw)) return this.DELIVERED;
    if (['in_transit', 'picked_up', 'dispatched', 'out_for_delivery'].includes(raw)) return this.IN_TRANSIT;
    if (['cancelled', 'cancelled_by_merchant', 'order_cancelled'].includes(raw)) return this.CANCELLED;
    if (['returned', 'return_in_transit', 'returning_to_merchant'].includes(raw)) return this.RETURNED;
    if (['pending', 'in_review', 'order_placed', 'assigned_for_pickup'].includes(raw)) return this.PENDING;
    return this.UNKNOWN;
  }
};

export class CourierFactory {
  static create(provider, config = {}) {
    const p = String(provider).toLowerCase().trim();
    switch (p) {
      case 'steadfast':
        return new SteadfastClient(config);
      case 'redx':
        return new RedXClient(config);
      case 'pathao':
        return new PathaoClient(config);
      case 'paperfly':
        return new PaperflyClient(config);
      case 'carrybee':
        return new CarrybeeClient(config);
      case 'ecourier':
        return new EcourierClient(config);
      default:
        throw new Error(`Unsupported courier provider: ${provider}. Supported: steadfast, redx, pathao, paperfly, carrybee, ecourier.`);
    }
  }
}

class BaseAdapter {
  constructor(config = {}) {
    this.config = config;
  }

  calculateRate(weightKg = 1.0, zone = 'inside_dhaka', codAmount = 0) {
    const isDhaka = zone === 'inside_dhaka';
    const isSuburb = zone === 'dhaka_suburb';
    const base = isDhaka ? 70 : (isSuburb ? 100 : 135);
    const extraKg = Math.max(0, Math.ceil(weightKg - 1.0));
    const extraCharge = extraKg * (isDhaka ? 20 : 25);
    const codCharge = Math.max(0, Math.round(codAmount * 0.01));
    return {
      baseCharge: base,
      extraCharge,
      codCharge,
      totalCharge: base + extraCharge + codCharge,
      estimatedSla: isDhaka ? '24 Hours' : '48-72 Hours'
    };
  }
}

export class SteadfastClient extends BaseAdapter {
  async createOrder(order) {
    return {
      success: true,
      provider: 'steadfast',
      consignmentId: 'STDF-' + Date.now(),
      trackingCode: 'STDF-' + Date.now(),
      status: CourierStatus.PENDING,
      amountToCollect: order.amountToCollect || 0,
      recipientPhone: order.recipientPhone
    };
  }
}

export class RedXClient extends BaseAdapter {
  async createOrder(order) {
    return {
      success: true,
      provider: 'redx',
      consignmentId: 'RDX-' + Date.now(),
      trackingCode: 'RDX-' + Date.now(),
      status: CourierStatus.PENDING,
      amountToCollect: order.amountToCollect || 0,
      recipientPhone: order.recipientPhone
    };
  }
}

export class PathaoClient extends BaseAdapter {
  async createOrder(order) {
    return {
      success: true,
      provider: 'pathao',
      consignmentId: 'PTH-' + Date.now(),
      trackingCode: 'PTH-' + Date.now(),
      status: CourierStatus.PENDING,
      amountToCollect: order.amountToCollect || 0,
      recipientPhone: order.recipientPhone
    };
  }
}

export class PaperflyClient extends BaseAdapter {}
export class CarrybeeClient extends BaseAdapter {}
export class EcourierClient extends BaseAdapter {}

export default {
  CourierFactory,
  CourierStatus
};
