# 06. Real-Time Webhooks & Events

Paperfly features a webhook engine capable of dispatching instant HTTP `POST` notifications to your server across **14 distinct shipment milestones**.

---

## ⚙️ Webhook Configuration

1. Log in to [go.paperfly.com.bd](https://go.paperfly.com.bd/merchant/developer-guide).
2. Go to **Developer Guide &rarr; Webhooks &rarr; Configuration**.
3. Fill in:
   - **Webhook Endpoint URL**: Your server's HTTPS route (e.g. `https://yourdomain.com/api/webhooks/paperfly`).
   - **Secret Key**: A secure random string used to verify incoming webhook signatures.
4. Select desired event subscriptions and click **Save Configuration**.

---

## 📋 Comprehensive Catalog of 14 Webhook Events

### 1. Order Lifecycle (3 Events)

| Event Name | Trigger Condition |
| :--- | :--- |
| `parcel.created` | Emitted immediately when an order is booked via API or Merchant Panel. |
| `parcel.invoiced` | Emitted when Paperfly compiles the financial invoice for the delivery fee. |
| `parcel.cancelled` | Emitted when an un-picked order is cancelled by merchant. |

### 2. Pickup & Transit (3 Events)

| Event Name | Trigger Condition |
| :--- | :--- |
| `parcel.picked_up` | Handshake completed; rider scans package at merchant warehouse. |
| `parcel.in_transit` | Consignment enters line-haul transit between sorting hubs. |
| `parcel.received_at_point` | Parcel arrives at the local destination distribution point. |

### 3. Delivery Phase (5 Events)

| Event Name | Trigger Condition |
| :--- | :--- |
| `parcel.assigned_for_delivery` | Package assigned to last-mile rider ("Out for Delivery"). |
| `parcel.delivered` | Recipient successfully accepts parcel and completes payment. |
| `parcel.partial` | Recipient accepts a partial portion of a multi-package shipment. |
| `parcel.exchange` | Exchange order completed: replacement delivered and return item collected. |
| `parcel.on_hold` | Delivery postponed per customer request or unreachable phone. |

### 4. Returns & Reverse Journey (3 Events)

| Event Name | Trigger Condition |
| :--- | :--- |
| `parcel.return` | Delivery aborted; return action initiated. |
| `parcel.return_transit` | Consignment is traversing reverse logistics line-haul. |
| `parcel.return_to_merchant` | Parcel physically returned and handed back to merchant store. |

---

## 📦 Webhook Payload Schema

Paperfly transmits JSON payloads structured as follows:

```json
{
  "event": "parcel.delivered",
  "timestamp": "2026-04-11T14:05:30+00:00",
  "data": {
    "order_number": "Z-051125-63821-A3-A1",
    "merchant_order_reference": "INV_2026_9041",
    "barcode": "751820115459",
    "package_price": 2500,
    "recipient": {
      "name": "Liton Ovi",
      "phone": "01610202717",
      "address": "Banani, Dhaka"
    },
    "special_instruction": ""
  }
}
```

---

## 🛡️ Operational Specifications & Retry Policy

1. **Timestamps**: All timestamps are formatted according to **ISO 8601 (UTC)**.
2. **HTTP Timeout**: Webhook calls enforce a strict **30-second socket timeout**.
3. **Acknowledgement**: Your endpoint must respond with an **HTTP `200 OK`** promptly.
4. **Retry Schedule**: If your server responds with `5xx` or times out, Paperfly will automatically retry delivery **up to 3 times** with exponential backoff.

---

## 💻 Sample Webhook Handlers

### Node.js / Express Example

```javascript
const express = require('express');
const app = express();
app.use(express.json());

app.post('/api/webhooks/paperfly', (req, res) => {
  const secret = req.headers['x-paperfly-secret'] || req.headers['secret-key'];
  if (secret !== process.env.PAPERFLY_WEBHOOK_SECRET) {
    return res.status(401).json({ error: 'Unauthorized secret token' });
  }

  const { event, timestamp, data } = req.body;
  console.log(`[Paperfly Webhook] Event: ${event} for Order ${data.merchant_order_reference}`);

  switch (event) {
    case 'parcel.delivered':
      // Update database order to delivered & record COD collected
      break;
    case 'parcel.return_to_merchant':
      // Restock items in inventory
      break;
    case 'parcel.assigned_for_delivery':
      // Send automated SMS to customer
      break;
  }

  return res.status(200).json({ received: true });
});
```

### Laravel PHP Example

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaperflyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = $request->header('x-paperfly-secret') ?? $request->header('secret-key');
        if ($secret !== config('services.paperfly.webhook_secret')) {
            return response()->json(['error' => 'Invalid secret key'], 401);
        }

        $event = $request->input('event');
        $orderData = $request->input('data');
        $reference = $orderData['merchant_order_reference'] ?? null;

        if ($event === 'parcel.delivered') {
            // Order::where('invoice_id', $reference)->update(['status' => 'delivered']);
        }

        return response()->json(['status' => 'success']);
    }
}
```
