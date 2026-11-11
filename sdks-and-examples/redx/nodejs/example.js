/**
 * RedX Node.js Client Example
 */

const RedxClient = require('./redx');

const token = process.env.REDX_JWT_TOKEN || 'YOUR_SANDBOX_JWT_TOKEN';
const redx = new RedxClient(token, true); // sandbox = true

async function runDemo() {
  console.log('=== RedX Node.js Integration Demo ===\n');

  try {
    // 1. Calculate shipping charge
    console.log('1. Calculating parcel charge...');
    const charge = await redx.calculateCharge(12, 1, 1500, 750);
    console.log(`Delivery Charge: BDT ${charge.deliveryCharge}, COD Charge: BDT ${charge.codCharge}`);

    // 2. Discover pickup stores
    console.log('\n2. Fetching registered pickup stores...');
    const stores = await redx.getPickupStores();
    console.log(`Found ${(stores.pickup_stores || []).length} registered warehouses.`);

    // 3. Create parcel
    console.log('\n3. Placing test parcel...');
    const booking = await redx.createParcel({
      customer_name: 'Tanvir Hossain',
      customer_phone: '01811223344',
      delivery_area: 'Mirpur DOHS',
      delivery_area_id: 12,
      customer_address: 'Road 5, House 10, Mirpur DOHS, Dhaka',
      merchant_invoice_id: `INV-${Date.now()}`,
      cash_collection_amount: '2000',
      paid_return_charge: 0,
      parcel_weight: 800,
      instruction: 'Deliver during daytime',
      value: 2000,
      is_closed_box: true,
      pickup_store_id: 1,
      parcel_details_json: [
        { name: 'Denim Jeans', category: 'Apparel', value: 2000 }
      ]
    });
    console.log(`Created! Tracking ID: ${booking.tracking_id}`);

  } catch (err) {
    console.error('RedX Error:', err.message, err.details);
  }
}

runDemo();
