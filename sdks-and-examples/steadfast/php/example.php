<?php

require_once __DIR__ . '/SteadfastClient.php';

use Steadfast\SteadfastClient;

// Initialize client with credentials
$client = new SteadfastClient(
    apiKey: getenv('STEADFAST_API_KEY') ?: 'YOUR_API_KEY',
    secretKey: getenv('STEADFAST_SECRET_KEY') ?: 'YOUR_SECRET_KEY'
);

echo "1. Checking Gateway Health (Ping)...\n";
$ping = $client->ping();
print_r($ping);

echo "\n2. Fetching Current Balance...\n";
$balance = $client->getBalance();
print_r($balance);

echo "\n3. Checking Customer Risk Score...\n";
$fraud = $client->checkFraudScore('01712345678');
print_r($fraud);

// echo "\n4. Booking Parcel...\n";
// $order = $client->createOrder([
//     'invoice'           => 'ORD-' . time(),
//     'recipient_name'    => 'Tanvir Ahmed',
//     'recipient_phone'   => '01811223344',
//     'recipient_address' => 'House 5, Road 2, Banani, Dhaka',
//     'cod_amount'        => 1200,
//     'note'              => 'Please deliver after 3 PM',
// ]);
// print_r($order);
