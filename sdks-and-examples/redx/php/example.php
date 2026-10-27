<?php
declare(strict_types=1);

require_once __DIR__ . '/RedxClient.php';

use RefatBd\Redx\RedxClient;

// Initialize client with Sandbox credentials
$jwtToken = getenv('REDX_JWT_TOKEN') ?: 'YOUR_SANDBOX_JWT_TOKEN';
$client   = new RedxClient($jwtToken, true);

echo "=== RedX Courier API Integration Demo ===\n";

try {
    // 1. Estimate delivery charge
    echo "\n1. Calculating Delivery Charge for 750g parcel...\n";
    $charge = $client->calculateCharge(12, 1, 1500.0, 750.0);
    echo "Delivery Charge: BDT {$charge['deliveryCharge']} | COD Charge: BDT {$charge['codCharge']}\n";

    // 2. Fetch areas by postal code
    echo "\n2. Fetching areas for Post Code 1206...\n";
    $areas = $client->getAreasByPostCode(1206);
    echo "Found " . count($areas['areas'] ?? []) . " sub-areas.\n";

    // 3. Create a parcel
    echo "\n3. Booking a parcel...\n";
    $parcel = $client->createParcel([
        'customer_name'          => 'Rahim Ahmed',
        'customer_phone'         => '01711223344',
        'delivery_area'          => 'Mirpur DOHS',
        'delivery_area_id'       => 12,
        'customer_address'       => 'House 45, Road 7, Avenue 3, Mirpur DOHS, Dhaka',
        'merchant_invoice_id'    => 'INV-' . time(),
        'cash_collection_amount' => '1500',
        'paid_return_charge'     => 0,
        'parcel_weight'          => 750,
        'instruction'            => 'Call before delivery',
        'value'                  => 1500,
        'is_closed_box'          => true,
        'pickup_store_id'        => 1,
        'parcel_details_json'    => [
            [
                'name'     => 'Leather Wallet',
                'category' => 'Accessories',
                'value'    => 1500,
            ]
        ]
    ]);
    $trackingId = $parcel['tracking_id'] ?? 'N/A';
    echo "Successfully Booked! Tracking ID: {$trackingId}\n";

    // 4. Track the parcel
    if ($trackingId !== 'N/A') {
        echo "\n4. Fetching tracking timeline...\n";
        $tracking = $client->trackParcel($trackingId);
        foreach ($tracking['tracking'] ?? [] as $event) {
            echo "[{$event['time']}] {$event['message_en']} ({$event['message_bn']})\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
