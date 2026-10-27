import { SteadfastClient } from './steadfast.js';

const client = new SteadfastClient({
  apiKey: 'YOUR_API_KEY',
  secretKey: 'YOUR_SECRET_KEY',
});

async function main() {
  console.log('--- 1. Service Health (Ping) ---');
  const ping = await client.ping();
  console.log(ping);

  console.log('\n--- 2. Live Balance ---');
  const balance = await client.getBalance();
  console.log(balance);

  console.log('\n--- 3. Fraud Risk Check ---');
  const fraud = await client.checkFraudScore('01712345678');
  console.log(fraud);
}

main().catch(console.error);
