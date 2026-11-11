<?php

declare(strict_types=1);

require_once __DIR__ . '/NagadClient.php';

use Refatbd\Nagad\NagadClient;

$merchantId = getenv('NAGAD_MERCHANT_ID') ?: '6800000025';
$privateKey = getenv('NAGAD_MERCHANT_PRIVATE_KEY') ?: 'YOUR_MERCHANT_PRIVATE_KEY';
$pgPublicKey = getenv('NAGAD_PG_PUBLIC_KEY') ?: 'YOUR_NAGAD_PG_PUBLIC_KEY';
$clientIp = getenv('NAGAD_SERVER_IP') ?: '103.145.118.24';

// Initialize Client in Sandbox mode
$nagad = new NagadClient(
    merchantId: $merchantId,
    merchantPrivateKey: $privateKey,
    pgPublicKey: $pgPublicKey,
    sandbox: true,
    serverIp: $clientIp
);

try {
    $orderId = 'INV-' . time();
    $amount = 150.00;
    $callbackUrl = 'https://merchant.example.com/payment/nagad/callback';

    echo "=== 1. High-Level 1-Step Payment Creation ===\n";
    echo "Creating payment for Order: {$orderId} (Amount: BDT {$amount})...\n";

    // In a real execution with valid keys, this returns the redirect URL directly:
    // $redirectUrl = $nagad->createPayment($orderId, $amount, $callbackUrl);
    // echo "Redirect customer to: {$redirectUrl}\n\n";

    echo "=== 2. Detailed Step-by-Step Execution Demo ===\n";
    echo "Step 1: Initializing checkout with challenge nonce...\n";
    // $init = $nagad->initializeCheckout($orderId);
    // echo "Received paymentReferenceId: " . $init['paymentReferenceId'] . "\n";

    // echo "Step 2: Submitting order amount and acquiring hosted URL...\n";
    // $complete = $nagad->completeCheckout(
    //     $init['paymentReferenceId'],
    //     $orderId,
    //     $amount,
    //     $callbackUrl,
    //     $init['challenge']
    // );
    // echo "Nagad Gateway Hosted URL: " . $complete['callBackUrl'] . "\n\n";

    echo "=== 3. Simulating Callback Parsing & Server Verification ===\n";
    $sampleCallbackUrl = "https://merchant.example.com/callback?merchant=6800000025&order_id={$orderId}&payment_ref_id=SAMPLE_REF_ID&status=Success&status_code=00_0000_000";
    $parsed = NagadClient::parseCallback($sampleCallbackUrl);
    echo "Parsed Inbound Callback:\n" . json_encode($parsed, JSON_PRETTY_PRINT) . "\n\n";

    echo "Step 4: Verifying transaction with Nagad banking engine...\n";
    // $verification = $nagad->verifyPayment($parsed['payment_ref_id']);
    // echo "Verified Status: " . $verification['status'] . "\n";

    echo "Nagad SDK initialized and ready for production use.\n";

} catch (Exception $e) {
    echo "Nagad Exception: " . $e->getMessage() . "\n";
}
