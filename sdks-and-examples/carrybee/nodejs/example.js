/**
 * Carrybee Node.js SDK Example
 * Author: Refat Rahman (https://refat.ovh | @refatbd)
 */

const { CarrybeeClient } = require('./carrybee');

const client = new CarrybeeClient({
  clientId: process.env.CARRYBEE_CLIENT_ID || 'YOUR_CLIENT_ID',
  clientSecret: process.env.CARRYBEE_CLIENT_SECRET || 'YOUR_CLIENT_SECRET',
  clientContext: process.env.CARRYBEE_CLIENT_CONTEXT || 'YOUR_CLIENT_CONTEXT',
  sandbox: true
});

async function main() {
  try {
    console.log('=== 1. Fetching Cities ===');
    const cities = await client.getCities();
    console.log(`Fetched ${cities.data ? cities.data.length : 0} cities.`);

    console.log('\n=== 2. Resolving Address Details ===');
    const resolved = await client.resolveAddressDetails('House 12, Road 4, Sector 7, Uttara, Dhaka');
    console.log('Address Resolution:', resolved);

    console.log('\n=== 3. Creating Single Order ===');
    const order = await client.createOrder({
      store_id: 101,
      merchant_order_id: `INV-NODE-${Date.now()}`,
      recipient_name: 'Refat Rahman',
      recipient_phone: '01811223344',
      recipient_address: 'House 14, Road 3, Dhanmondi, Dhaka',
      recipient_city: 1,
      recipient_zone: 5,
      delivery_type: 1, // Normal
      product_type: 1,  // Parcel
      item_weight: 1000,
      item_description: 'Node.js Developer Hoodie',
      item_quantity: 1,
      collectable_amount: 1500,
      special_instruction: 'Deliver during office hours'
    });
    console.log('Created Order:', order);

    if (order && order.consignment_id) {
      console.log(`\n=== 4. Tracking Consignment: ${order.consignment_id} ===`);
      const tracking = await client.trackOrder(order.consignment_id);
      console.log('Tracking Result:', tracking);
    }
  } catch (err) {
    console.error('Error occurred:', err.message, err.data || '');
  }
}

if (require.main === module) {
  main();
}
