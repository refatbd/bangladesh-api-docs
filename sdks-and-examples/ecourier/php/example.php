<?php

declare(strict_types=1);

require_once __DIR__ . '/EcourierClient.php';

use Refatbd\Ecourier\EcourierClient;

$apiKey = getenv('ECOURIER_API_KEY') ?: 'YOUR_API_KEY';
$apiSecret = getenv('ECOURIER_API_SECRET') ?: 'YOUR_API_SECRET';
$userId = getenv('ECOURIER_USER_ID') ?: 'YOUR_USER_ID';

// Initialize Client in Staging environment
$ecourier = new EcourierClient($apiKey, $apiSecret, $userId, true);

try {
    echo "=== 1. Checking Active Package Codes ===\n";
    $packages = $ecourier->getPackages();
    echo "Active Packages Count: " . count($packages) . "\n";
    if (!empty($packages)) {
        echo "Primary Package: " . $packages[0]['package_name'] . " (" . $packages[0]['package_code'] . ")\n\n";
    }

    echo "=== 2. Fetching Cities ===\n";
    $cities = $ecourier->getCities();
    echo "Found " . count($cities) . " serviceable cities.\n\n";

    echo "=== 3. AI Fraud Check on Customer Number ===\n";
    $fraud = $ecourier->checkFraudStatus('01811223344');
    echo "Customer Risk Status: " . ($fraud['customer_status'] ?? 'N/A') . "\n\n";

    echo "=== 4. Placing Consignment Order ===\n";
    $order = $ecourier->placeOrder([
        'recipient_name' => 'Refat Rahman',
        'recipient_mobile' => '01811223344',
        'recipient_city' => 'Dhaka',
        'recipient_thana' => 'Badda',
        'recipient_area' => 'Middle Badda',
        'recipient_address' => 'House 14, Road 3, Block D',
        'recipient_zip' => '1212',
        'package_code' => '#2431',
        'product_price' => 1850,
        'payment_method' => 'COD',
        'parcel_type' => 'BOX',
        'product_id' => 'INV-PHP-' . time(),
        'comments' => 'Handle with care',
        'is_fragile' => 0,
        'sending_type' => 1,
        'is_ipay' => 0
    ]);
    echo "Order Response: " . json_encode($order, JSON_PRETTY_PRINT) . "\n\n";

    if (!empty($order['ID'])) {
        $trackingId = $order['ID'];
        echo "=== 5. Tracking Consignment: {$trackingId} ===\n";
        $tracking = $ecourier->trackByEcr($trackingId);
        echo "Tracking Result: " . json_encode($tracking, JSON_PRETTY_PRINT) . "\n\n";

        echo "=== 6. Generating Thermal Label Print URL ===\n";
        $label = $ecourier->getLabelPrintUrl($trackingId);
        echo "Label PDF URL: " . ($label['query_data'] ?? 'N/A') . "\n\n";
    }

} catch (Exception $e) {
    echo "Error (" . $e->getCode() . "): " . $e->getMessage() . "\n";
}
