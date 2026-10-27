# Unified Bangladesh Courier Engine (`refatbd/bangladesh-courier`)

Every logistics provider in Bangladesh invents their own API naming conventions:
- Some require weight in **kilograms** (`1.5`), while others demand **grams** (`1500`).
- Recipient names are called `recipient_name`, `customer_name`, or `customer_name_en`.
- Delivery status strings fluctuate wildly: `delivered`, `DELIVERED`, `Delivered`, `Delivered_Paid`.
- Webhook payloads vary from raw JSON arrays to SHA-256 HMAC tokens and mandatory HTTP `202 Accepted` echo bodies.

To solve this fragmentation, **Refat Rahman** (`@refatbd`) created the **Unified Bangladesh Courier Engine** — a standardized PSR-4 PHP and Node.js adapter library allowing developers to swap or orchestrate logistics partners with a single interface.

---

## Architecture Overview

```
                      ┌─────────────────────────────────┐
                      │    Your Application / Controller│
                      └────────────────┬────────────────┘
                                       │
                                       ▼
                      ┌─────────────────────────────────┐
                      │    Courier::provider('...')     │
                      └────────────────┬────────────────┘
                                       │
         ┌──────────────┬──────────────┼──────────────┬──────────────┐
         ▼              ▼              ▼              ▼              ▼
   ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌───────────┐
   │ Steadfast │  │   RedX    │  │  Pathao   │  │ Paperfly  │  │ eCourier  │
   │  Adapter  │  │  Adapter  │  │  Adapter  │  │  Adapter  │  │  Adapter  │
   └─────┬─────┘  └─────┬─────┘  └─────┬─────┘  └─────┬─────┘  └─────┬─────┘
         │              │              │              │              │
         ▼              ▼              ▼              ▼              ▼
   Steadfast API    RedX API      Pathao API    Paperfly API   eCourier API
```

---

## Installation

### PHP (Composer)
```bash
composer require refatbd/bangladesh-courier
```

### Node.js (npm / yarn)
```bash
npm install @refatbd/bd-courier
```

---

## PHP Standard Usage

### 1. Initialize with Credentials

```php
use Refatbd\Courier\Courier;

// Factory instantiate using environment credentials
$courier = Courier::provider('steadfast', [
    'api_key'    => env('STEADFAST_API_KEY'),
    'secret_key' => env('STEADFAST_SECRET_KEY'),
]);

// Or switch effortlessly to Pathao
$pathao = Courier::provider('pathao', [
    'client_id'     => env('PATHAO_CLIENT_ID'),
    'client_secret' => env('PATHAO_CLIENT_SECRET'),
    'username'      => env('PATHAO_USER'),
    'password'      => env('PATHAO_PASS'),
]);
```

### 2. Standardized Order Creation

Notice how you pass the exact same payload regardless of whether you are shipping with Steadfast, RedX, Pathao, or Paperfly:

```php
use Refatbd\Courier\DTO\OrderRequest;

$order = $courier->createOrder(new OrderRequest([
    'invoice_id'        => 'INV-9021',
    'recipient_name'    => 'Refat Rahman',
    'recipient_phone'   => '01700000000',
    'recipient_address' => 'House 12, Road 4, Dhanmondi, Dhaka',
    'delivery_zone'     => 'inside_dhaka',
    'amount_to_collect' => 2500,  // COD in BDT (0 for prepaid)
    'weight_kg'         => 1.5,   // Engine automatically converts to 1500g for RedX/Paperfly
    'special_notes'     => 'Handle fragile electronics with care',
]));

// Unified Response Object
echo $order->tracking_code;   // E.g., 'STDF-881920'
echo $order->consignment_id;  // E.g., '1029481'
echo $order->status;          // Always standardized: 'pending', 'in_transit', 'delivered'
echo $order->delivery_fee;    // Calculated merchant charge
```

### 3. Unified Real-Time Tracking

```php
$tracking = $courier->trackOrder('STDF-881920');

echo $tracking->current_status; // Standard status enum: 'DELIVERED', 'IN_TRANSIT', 'CANCELLED'
echo $tracking->recipient_phone;
echo $tracking->updated_at;

foreach ($tracking->milestones as $step) {
    echo "{$step->timestamp} - {$step->title}: {$step->message}\n";
}
```

### 4. Normalized Status Enum Mapping

| Raw Provider Status | Unified Engine Status | Description |
| :--- | :--- | :--- |
| `in_review`, `pending`, `PLACED` | `Status::PENDING` | Order registered, awaiting warehouse pickup |
| `in_transit`, `picked_up`, `dispatched` | `Status::IN_TRANSIT` | Rider picked up or shipment at sorting hub |
| `delivered`, `DELIVERED`, `Delivered_Paid` | `Status::DELIVERED` | Parcel successfully handed over and COD received |
| `cancelled`, `cancelled_by_merchant` | `Status::CANCELLED` | Order voided before courier dispatch |
| `returned`, `return_in_transit`, `RET` | `Status::RETURNED` | Delivery rejected by customer; returning to store |

---

## Node.js Standard Usage

```javascript
import { CourierFactory } from '@refatbd/bd-courier';

const courier = CourierFactory.create('pathao', {
  clientId: process.env.PATHAO_CLIENT_ID,
  clientSecret: process.env.PATHAO_CLIENT_SECRET,
  username: process.env.PATHAO_USER,
  password: process.env.PATHAO_PASS,
});

const consignment = await courier.createOrder({
  invoiceId: 'INV-1092',
  recipientName: 'Ariful Islam',
  recipientPhone: '01812345678',
  recipientAddress: 'Gulshan 2, Dhaka',
  amountToCollect: 1200,
  weightKg: 0.8,
});

console.log('Tracking URL:', consignment.trackingUrl);
```

---

## Webhook Auto-Dispatcher & Echo Handshake

Different couriers have tricky webhook requirements (for instance, Carrybee will deactivate your webhook endpoint if your server does not return `{"message": "received"}` within 1500ms).

The Unified Engine handles signature verification and responses automatically:

```php
// routes/api.php
Route::post('/webhooks/courier/{provider}', function ($provider, Request $request) {
    $courier = Courier::provider($provider);

    // Verifies signatures (Carrybee 202 echo, Paperfly HMAC, etc.)
    $event = $courier->handleWebhook($request);

    // Update your database using unified status
    Order::where('tracking_code', $event->trackingCode)->update([
        'status' => $event->status,
    ]);

    return $event->respond();
});
```
