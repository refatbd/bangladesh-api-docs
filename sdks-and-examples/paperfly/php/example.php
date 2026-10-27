<?php
declare(strict_types=1);

require_once __DIR__ . '/PaperflyClient.php';

use RefatBd\Paperfly\PaperflyClient;

$client = new PaperflyClient(
    username: getenv('PAPERFLY_USERNAME') ?: 'sellerone',
    password: getenv('PAPERFLY_PASSWORD') ?: 'YOUR_PASSWORD'
);

echo "=== Paperfly Courier Integration Demo ===\n";

try {
    // 1. Create a Standard Order
    echo "\n1. Booking Standard Order...\n";
    $refNumber = 'INV-' . time();
    $order = $client->createOrder([
        'merchantOrderReference' => $refNumber,
        'storeName'              => 'Apex Banani',
        'productBrief'           => 'Leather Loafers',
        'packagePrice'           => '2800',
        'max_weight'             => '1.0',
        'customerName'           => 'Liton Ovi',
        'customerAddress'        => 'House 12, Road 4, Banani, Dhaka',
        'customerPhone'          => '01610202717'
    ]);
    print_r($order);

    // 2. Track the Order
    echo "\n2. Tracking Order ({$refNumber})...\n";
    $tracking = $client->trackOrder($refNumber);
    print_r($tracking);

    // 3. Create Exchange Order Demo
    echo "\n3. Booking Exchange Order...\n";
    $exchange = $client->createExchangeOrder([
        'merchantOrderReference' => 'EXC-' . time(),
        'storeName'              => 'Apex Banani',
        'productBrief'           => 'Replacement Size 43',
        'packagePrice'           => '0',
        'max_weight'             => '1.0',
        'customerName'           => 'Liton Ovi',
        'customerAddress'        => 'House 12, Road 4, Banani, Dhaka',
        'customerPhone'          => '01610202717',
        'exchangeDescription'    => 'Old Size 42 Shoes (Return)',
        'exchangePrice'          => '100',
        'exchangeWeight'         => '1.0'
    ]);
    print_r($exchange);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
