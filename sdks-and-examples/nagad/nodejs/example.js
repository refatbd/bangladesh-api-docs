/**
 * Nagad Node.js SDK Usage Example
 * Author: Refat Rahman (https://refat.ovh | @refatbd)
 */

const { NagadClient } = require('./nagad');

const client = new NagadClient({
  merchantId: process.env.NAGAD_MERCHANT_ID || '6800000025',
  merchantPrivateKey: process.env.NAGAD_MERCHANT_PRIVATE_KEY || 'YOUR_PRIVATE_KEY',
  pgPublicKey: process.env.NAGAD_PG_PUBLIC_KEY || 'YOUR_PG_PUBLIC_KEY',
  sandbox: true,
  serverIp: process.env.NAGAD_SERVER_IP || '103.145.118.24'
});

async function main() {
  try {
    const orderId = `INV-NODE-${Date.now()}`;
    const amount = 250.00;
    const callbackUrl = 'https://merchant.example.com/payment/nagad/callback';

    console.log('=== 1. High-Level 1-Step Payment Creation ===');
    console.log(`Initiating payment for Order: ${orderId} (BDT ${amount})...`);

    // In a live integration with valid merchant keys:
    // const redirectUrl = await client.createPayment(orderId, amount, callbackUrl);
    // console.log(`Redirect customer to Nagad Gateway: ${redirectUrl}`);

    console.log('\n=== 2. Parsing Inbound Callback ===');
    const sampleCallback = `https://merchant.example.com/callback?merchant=6800000025&order_id=${orderId}&payment_ref_id=SAMPLE_REF_ID&status=Success&status_code=00_0000_000`;
    const parsed = NagadClient.parseCallback(sampleCallback);
    console.log('Parsed Callback Parameters:', parsed);

    console.log('\n=== 3. Server-to-Server Verification (Step 4) ===');
    // const verification = await client.verifyPayment(parsed.payment_ref_id);
    // console.log('Authoritative Status:', verification.status);

    console.log('Nagad Node.js SDK initialized successfully.');

  } catch (err) {
    console.error('Nagad Error:', err.message);
  }
}

if (require.main === module) {
  main();
}
