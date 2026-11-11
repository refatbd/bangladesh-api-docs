/**
 * eCourier Node.js SDK Usage Example
 * Author: Refat Rahman (https://refat.ovh | @refatbd)
 */

const { EcourierClient } = require('./ecourier');

const client = new EcourierClient({
  apiKey: process.env.ECOURIER_API_KEY || 'YOUR_API_KEY',
  apiSecret: process.env.ECOURIER_API_SECRET || 'YOUR_API_SECRET',
  userId: process.env.ECOURIER_USER_ID || 'YOUR_USER_ID',
  sandbox: true
});

async function main() {
  try {
    console.log('=== 1. Fetching Assigned Packages ===');
    const packages = await client.getPackages();
    console.log(`Retrieved ${packages.length} package options.`);

    console.log('\n=== 2. AI Fraud Assessment on Phone ===');
    const fraud = await client.checkFraudStatus('01811223344');
    console.log('Fraud Risk Assessment:', fraud);

    console.log('\n=== 3. Creating Single Order ===');
    const order = await client.placeOrder({
      recipient_name: 'Refat Rahman',
      recipient_mobile: '01811223344',
      recipient_city: 'Dhaka',
      recipient_thana: 'Badda',
      recipient_area: 'Middle Badda',
      recipient_address: 'House 14, Road 3, Block D',
      recipient_zip: '1212',
      package_code: '#2431',
      product_price: 1850,
      payment_method: 'COD',
      parcel_type: 'BOX',
      product_id: `INV-NODE-${Date.now()}`,
      comments: 'Delivery via Node.js SDK',
      is_fragile: 0,
      sending_type: 1,
      is_ipay: 0
    });
    console.log('Order Placement Result:', order);

    if (order && order.ID) {
      console.log(`\n=== 4. Tracking Consignment: ${order.ID} ===`);
      const tracking = await client.trackByEcr(order.ID);
      console.log('Tracking Result:', tracking);

      console.log('\n=== 5. Fetching Label PDF URL ===');
      const label = await client.getLabelPrintUrl(order.ID);
      console.log('PDF Label Link:', label.query_data);
    }
  } catch (err) {
    console.error('API Error:', err.message);
  }
}

if (require.main === module) {
  main();
}
