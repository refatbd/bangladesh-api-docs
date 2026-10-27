# 08. Real-Time Webhooks

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## ⚡ Why Use Webhooks?

Polling the tracking endpoint burns API limits and yields cached results due to Steadfast's **60-second status cache**. With Webhooks, Steadfast pushes events directly to your server endpoint the instant:
- A parcel changes status (e.g. from `in_transit` to `delivered` or `cancelled`).
- A return consignment is approved or received.
- A financial COD payout settlement is completed.

---

## 🛠️ Configuring Your Webhook URL

1. Log in to your Steadfast Merchant Dashboard.
2. In the left navigation, go to **Tools -> Webhook** (`https://steadfast.com.bd/user/webhook/add`).
3. Enter your server's secure public endpoint URL (e.g., `https://yourdomain.com/api/webhooks/steadfast`).
4. Select the events you want to listen to and click **Save Webhook**.

---

## 📥 Sample Webhook Payload

When an order status changes, Steadfast issues an HTTP `POST` to your webhook URL with the following JSON body:

```json
{
  "event": "parcel_status_updated",
  "consignment_id": 9845210,
  "invoice": "ORD-10231",
  "tracking_code": "ST-9845210",
  "status": "delivered",
  "cod_amount": 1060.00,
  "updated_at": "2026-10-27 11:15:00"
}
```

---

## 💻 Webhook Receiver Implementations

### PHP / Laravel Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SteadfastWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Steadfast Webhook Received', $payload);

        $invoice = $payload['invoice'] ?? null;
        $status  = $payload['status'] ?? null;

        if (! $invoice || ! $status) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        // Find and update your order
        $order = SalesOrder::where('order_number', $invoice)->first();
        if ($order) {
            if ($status === 'delivered') {
                $order->update(['status' => 'completed']);
            } elseif ($status === 'cancelled') {
                $order->update(['status' => 'cancelled']);
            }
        }

        // Always acknowledge with 200 OK
        return response()->json(['status' => 'success'], 200);
    }
}
```

### Node.js / Express Example

```javascript
const express = require('express');
const app = express();

app.use(express.json());

app.post('/api/webhooks/steadfast', (req, res) => {
  const { invoice, status, consignment_id, tracking_code } = req.body;

  console.log(`[Steadfast Webhook] Order ${invoice} updated to: ${status}`);

  // Process status update in your database...

  // Always return 200 OK immediately
  res.status(200).json({ received: true });
});

app.listen(3000, () => console.log('Webhook server listening on port 3000'));
```
