<?php

declare(strict_types=1);

require_once __DIR__ . '/CarrybeeClient.php';

use Refatbd\Carrybee\CarrybeeClient;

$clientId = getenv('CARRYBEE_CLIENT_ID') ?: 'YOUR_CLIENT_ID';
$clientSecret = getenv('CARRYBEE_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET';
$clientContext = getenv('CARRYBEE_CLIENT_CONTEXT') ?: 'YOUR_CLIENT_CONTEXT';

// Initialize Client in Sandbox mode
$carrybee = new CarrybeeClient($clientId, $clientSecret, $clientContext, true);

try {
    echo "=== 1. Fetching Cities ===\n";
    $cities = $carrybee->getCities();
    echo "Found " . count($cities['data'] ?? []) . " cities.\n";
    if (!empty($cities['data'])) {
        echo "First City: " . $cities['data'][0]['name'] . " (ID: " . $cities['data'][0]['id'] . ")\n\n";
    }

    echo "=== 2. Fetching Dhaka Zones ===\n";
    $zones = $carrybee->getZones(1);
    echo "Found " . count($zones['data'] ?? []) . " zones in Dhaka.\n\n";

    echo "=== 3. Querying Area Suggestion ===\n";
    $suggestions = $carrybee->getAreaSuggestion('Uttara');
    echo "Area suggestion returned: " . json_encode($suggestions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

    echo "=== 4. Creating Single Order ===\n";
    $order = $carrybee->createOrder([
        'store_id' => 101,
        'merchant_order_id' => 'INV-PHP-' . time(),
        'recipient_name' => 'Refat Rahman',
        'recipient_phone' => '01811223344',
        'recipient_address' => 'House 14, Road 3, Dhanmondi, Dhaka',
        'recipient_city' => 1,
        'recipient_zone' => 5,
        'delivery_type' => 1, // Normal
        'product_type' => 1,  // Parcel
        'item_weight' => 1500, // 1500 grams
        'item_description' => 'Cotton Polo T-Shirt',
        'item_quantity' => 1,
        'collectable_amount' => 1250,
        'special_instruction' => 'Call recipient before dispatching'
    ]);
    echo "Order Response: " . json_encode($order, JSON_PRETTY_PRINT) . "\n\n";

    if (!empty($order['consignment_id'])) {
        echo "=== 5. Tracking Consignment: {$order['consignment_id']} ===\n";
        $tracking = $carrybee->trackOrder($order['consignment_id']);
        echo "Tracking Status: " . ($tracking['status'] ?? 'N/A') . "\n\n";
    }

} catch (Exception $e) {
    echo "Error (" . $e->getCode() . "): " . $e->getMessage() . "\n";
}
