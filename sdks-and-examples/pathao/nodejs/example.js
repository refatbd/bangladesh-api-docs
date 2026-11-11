/**
 * Pathao Courier Node.js Integration Demo
 */

const PathaoClient = require('./pathao');

const client = new PathaoClient({
  clientId: process.env.PATHAO_CLIENT_ID || '7N1aMJQbWm',
  clientSecret: process.env.PATHAO_CLIENT_SECRET || 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
  username: process.env.PATHAO_USERNAME || 'test@pathao.com',
  password: process.env.PATHAO_PASSWORD || 'lovePathao',
  isSandbox: true,
});

async function main() {
  console.log('=== Pathao Courier Node.js Demo ===\n');

  try {
    // 1. Authenticate
    console.log('1. Obtaining OAuth Token...');
    const token = await client.issueToken();
    console.log(`Authenticated! Expires in ${token.expires_in} seconds.`);

    // 2. Fetch Cities
    console.log('\n2. Listing Cities...');
    const cities = await client.getCities();
    console.log(`Found ${(cities.data.data || []).length} cities.`);

    // 3. Price plan
    console.log('\n3. Estimating delivery fee...');
    const price = await client.calculatePrice({
      store_id: 4821,
      item_type: 2,
      delivery_type: 48,
      item_weight: 0.5,
      recipient_city: 1,
      recipient_zone: 1070
    });
    console.log(`Delivery: BDT ${price.data.final_price} (COD: ${price.data.cod_percentage * 100}%)`);

    // 4. Place order
    console.log('\n4. Creating single order...');
    const order = await client.createOrder({
      store_id: 4821,
      merchant_order_id: `INV-${Date.now()}`,
      recipient_name: 'Mahmudul Hasan',
      recipient_phone: '01811223344',
      recipient_address: 'House 10, Road 4, Sector 7, Uttara, Dhaka',
      recipient_city: 1,
      recipient_zone: 1070,
      recipient_area: 37,
      delivery_type: 48,
      item_type: 2,
      item_quantity: 1,
      item_weight: 0.5,
      amount_to_collect: 1500,
      item_description: 'Running Shoes'
    });
    console.log(`Order Placed! Consignment ID: ${order.data.consignment_id}`);

  } catch (err) {
    console.error('Pathao Error:', err.message, err.details);
  }
}

main();
