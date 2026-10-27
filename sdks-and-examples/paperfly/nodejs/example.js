/**
 * Paperfly Courier Node.js Integration Demo
 */

const PaperflyClient = require('./paperfly');

const client = new PaperflyClient({
  username: process.env.PAPERFLY_USERNAME || 'sellerone',
  password: process.env.PAPERFLY_PASSWORD || 'YOUR_PASSWORD',
});

async function runDemo() {
  console.log('=== Paperfly Node.js Integration Demo ===\n');

  try {
    // 1. Create a Standard Parcel
    console.log('1. Booking standard order...');
    const ref = `ORD-${Date.now()}`;
    const order = await client.createOrder({
      merchantOrderReference: ref,
      storeName: 'Main Store',
      productBrief: 'Denim Jacket',
      packagePrice: '1800',
      max_weight: '0.8',
      customerName: 'Siam Ahmed',
      customerAddress: 'Banani Road 11, Dhaka',
      customerPhone: '01711223344'
    });
    console.log('Success:', order);

    // 2. Track Order
    console.log(`\n2. Tracking order (${ref})...`);
    const tracking = await client.trackOrder(ref);
    console.log('Tracking Status:', JSON.stringify(tracking, null, 2));

  } catch (err) {
    console.error('Paperfly Error:', err.message, err.details);
  }
}

runDemo();
