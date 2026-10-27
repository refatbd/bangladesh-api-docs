<?php
declare(strict_types=1);

require_once __DIR__ . '/PathaoClient.php';

use RefatBd\Pathao\PathaoClient;

// Initialize Pathao client in Sandbox mode with default test credentials
$client = new PathaoClient(
    clientId:     getenv('PATHAO_CLIENT_ID')     ?: '7N1aMJQbWm',
    clientSecret: getenv('PATHAO_CLIENT_SECRET') ?: 'wRcaibZkUdSNz2EI9ZyuXLlNrnAv0TdPUPXMnD39',
    username:     getenv('PATHAO_USERNAME')      ?: 'test@pathao.com',
    password:     getenv('PATHAO_PASSWORD')      ?: 'lovePathao',
    isSandbox:    true
);

echo "=== Pathao Courier Integration Demo ===\n";

try {
    // 1. Authenticate & Obtain token
    echo "\n1. Authenticating with Pathao OAuth 2.0...\n";
    $tokenData = $client->issueToken();
    echo "Authenticated successfully! Token expires in {$tokenData['expires_in']} seconds.\n";

    // 2. Fetch Cities
    echo "\n2. Fetching Cities...\n";
    $cities = $client->getCities();
    echo "Fetched " . count($cities['data']['data'] ?? []) . " cities.\n";

    // 3. Estimate Price
    echo "\n3. Calculating Price Plan for 0.5kg package (Store 4821)...\n";
    $price = $client->calculatePrice(
        storeId:       4821,
        itemType:      2,   // Parcel
        deliveryType:  48,  // Normal
        itemWeight:    0.5,
        recipientCity: 1,   // Dhaka
        recipientZone: 1070 // Uttara
    );
    echo "Delivery Price: BDT {$price['data']['price']} | Final Price: BDT {$price['data']['final_price']}\n";

    // 4. Create an order
    echo "\n4. Booking a test order...\n";
    $order = $client->createOrder([
        'store_id'          => 4821,
        'merchant_order_id' => 'ORD-' . time(),
        'recipient_name'    => 'Tariqul Islam',
        'recipient_phone'   => '01711223344',
        'recipient_address' => 'House 14, Road 7, Sector 3, Uttara, Dhaka',
        'recipient_city'    => 1,
        'recipient_zone'    => 1070,
        'recipient_area'    => 37,
        'delivery_type'     => 48,
        'item_type'         => 2,
        'item_quantity'     => 1,
        'item_weight'       => 0.5,
        'amount_to_collect' => 1200,
        'item_description'  => 'Mens Casual Shirt'
    ]);
    $consignmentId = $order['data']['consignment_id'] ?? 'N/A';
    echo "Order Created! Consignment ID: {$consignmentId}\n";

    // 5. Track Order
    if ($consignmentId !== 'N/A') {
        echo "\n5. Querying Order Status...\n";
        $info = $client->getOrderInfo($consignmentId);
        echo "Status: {$info['data']['order_status']} ({$info['data']['order_status_slug']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
