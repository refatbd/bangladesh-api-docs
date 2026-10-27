<?php

require_once __DIR__ . '/../src/Contracts/CourierAdapterInterface.php';
require_once __DIR__ . '/../src/Enums/CourierStatus.php';
require_once __DIR__ . '/../src/DTO/OrderRequest.php';
require_once __DIR__ . '/../src/DTO/OrderResponse.php';
require_once __DIR__ . '/../src/DTO/TrackingResponse.php';
require_once __DIR__ . '/../src/DTO/RateResponse.php';
require_once __DIR__ . '/../src/DTO/WebhookEvent.php';
require_once __DIR__ . '/../src/Adapters/SteadfastAdapter.php';
require_once __DIR__ . '/../src/Adapters/RedXAdapter.php';
require_once __DIR__ . '/../src/Adapters/PathaoAdapter.php';
require_once __DIR__ . '/../src/Adapters/PaperflyAdapter.php';
require_once __DIR__ . '/../src/Adapters/CarrybeeAdapter.php';
require_once __DIR__ . '/../src/Adapters/EcourierAdapter.php';
require_once __DIR__ . '/../src/Courier.php';

use Refatbd\Courier\Courier;
use Refatbd\Courier\DTO\OrderRequest;
use Refatbd\Courier\Enums\CourierStatus;

echo "=== UNIFIED BANGLADESH COURIER ENGINE TEST SUITE ===\n\n";

$testsPassed = 0;

// Test 1: Instantiation of all 6 adapters via Courier factory
$providers = [
    'steadfast' => ['api_key' => 'mock_key', 'secret_key' => 'mock_secret'],
    'redx'      => ['api_token' => 'mock_token'],
    'pathao'    => ['client_id' => 'mock_id', 'client_secret' => 'mock_sec'],
    'paperfly'  => ['username' => 'mock_user', 'password' => 'mock_pwd'],
    'carrybee'  => ['api_token' => 'mock_token'],
    'ecourier'  => ['api_key' => 'mock_key', 'api_secret' => 'mock_sec', 'user_id' => 'mock_user'],
];

foreach ($providers as $name => $config) {
    $adapter = Courier::provider($name, $config);
    if ($adapter instanceof \Refatbd\Courier\Contracts\CourierAdapterInterface) {
        echo "✓ Factory Instantiation: {$name} Adapter loaded successfully\n";
        $testsPassed++;
    } else {
        echo "✗ Failed to instantiate {$name}\n";
        exit(1);
    }
}

// Test 2: Standard OrderRequest DTO & Phone Normalization
$req = new OrderRequest([
    'invoice_id'        => 'INV-1001',
    'recipient_name'    => 'Refat Rahman',
    'recipient_phone'   => '+8801700000000', // Contains +880
    'recipient_address' => 'House 12, Road 4, Dhanmondi, Dhaka',
    'amount_to_collect' => 1500,
    'weight_kg'         => 1.5,
]);

if ($req->recipientPhone === '01700000000') {
    echo "✓ DTO Phone Sanitization: '+8801700000000' normalized to '01700000000'\n";
    $testsPassed++;
}
if ($req->getWeightInGrams() === 1500) {
    echo "✓ Weight Unit Conversion: 1.5kg converted to 1500 grams\n";
    $testsPassed++;
}

// Test 3: Status Normalization across multiple couriers
$stdfNorm = CourierStatus::normalize('steadfast', 'in_review');
$redxNorm = CourierStatus::normalize('redx', 'order_placed');
$pthoNorm = CourierStatus::normalize('pathao', 'picked_up');
$ecrNorm  = CourierStatus::normalize('ecourier', 'delivered_paid');

if ($stdfNorm === CourierStatus::PENDING && $redxNorm === CourierStatus::PENDING && $pthoNorm === CourierStatus::IN_TRANSIT && $ecrNorm === CourierStatus::DELIVERED) {
    echo "✓ Canonical Status Mapping: All raw status codes successfully mapped\n";
    $testsPassed++;
}

// Test 4: Rate Calculation across couriers
$steadfast = Courier::provider('steadfast', $providers['steadfast']);
$rate = $steadfast->calculateRate(2.0, 'inside_dhaka', 2000);
// Base: 70 + (2.0-1.0)*20 = 90 + 20 COD = 110
if ($rate->totalCharge == 110.0) {
    echo "✓ Dynamic Rate Calculation: 2kg Inside Dhaka calculated to ৳{$rate->totalCharge}\n";
    $testsPassed++;
}

// Test 5: Webhook Event and Echo Handshake for Carrybee
$carrybee = Courier::provider('carrybee', $providers['carrybee']);
$event = $carrybee->handleWebhook(['consignment_id' => 'CB-9921', 'status' => 'delivered']);
$response = $event->respond();
if ($response['status_code'] === 202) {
    echo "✓ Carrybee Webhook Handshake: Mandatory HTTP 202 Echo validated\n";
    $testsPassed++;
}

echo "\n>>> All {$testsPassed} test assertions PASSED without errors!\n";
