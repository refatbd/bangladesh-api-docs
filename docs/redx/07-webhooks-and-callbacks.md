# 07. Real-Time Webhooks & Callbacks

RedX provides an event-driven webhook subsystem to push shipment updates directly to your e-commerce application. Instead of repeatedly polling the tracking API, RedX dispatches an HTTP `POST` request to your designated Callback URL whenever a parcel milestone changes.

---

## ⚙️ Webhook Configuration

1. Log in to the [RedX Merchant Dashboard](https://redx.com.bd/developer-api/).
2. Scroll to the **Webhook** section.
3. Enter your publicly accessible HTTPS callback endpoint into the **Enter your callback URL** input field.
4. Click **Save Link**.

### Callback URL Structure & Security

RedX sends authentication tokens directly within query string parameters:

```http
POST https://yourdomain.com/api/webhooks/redx?token=YOUR_SECRET_WEBHOOK_KEY
```

> [!TIP]
> Protect your endpoint by asserting that `req.query.token === process.env.REDX_WEBHOOK_SECRET` before processing the request body.

---

## 📦 Webhook Payload Format

RedX delivers updates with `Content-Type: application/json` containing the following schema:

```json
{
  "tracking_number": "21A427TU4BN3R",
  "timestamp": "2026-04-28T14:30:22.000Z",
  "status": "delivered",
  "message_en": "Parcels delivered by rider",
  "message_bn": "পার্সেলটি সফলভাবে ডেলিভারি হয়েছে",
  "invoice_number": "INV-2026-8801",
  "delivery_type": "regular"
}
```

### Payload Properties

| Property | Type | Description |
| :--- | :---: | :--- |
| `tracking_number` | `string` | The RedX unique tracking identifier. |
| `timestamp` | `string` | ISO 8601 UTC timestamp of the status update. |
| `status` | `string` | Standardized status code (e.g. `delivered`, `delivery-in-progress`). |
| `message_en` | `string` | English description of the milestone. |
| `message_bn` | `string` | Bengali description of the milestone. |
| `invoice_number` | `string` | The merchant invoice ID provided during parcel creation. |
| `delivery_type` | `string` | Logistics execution category (`regular`, `exchange-delivery`, etc.). |

---

## 🚦 Status Mapping Table

| `status` | Official Meaning | Recommended Merchant Action |
| :--- | :--- | :--- |
| `ready-for-delivery` | Parcel received from merchant at hub | Mark order as "In Transit" |
| `delivery-in-progress`| Parcel dispatched to rider for last-mile delivery | Notify customer via SMS |
| `delivered` | Parcel successfully delivered by rider | Mark order as "Completed" |
| `agent-hold` | Parcel on hold with delivery agent (e.g. customer requested delay) | Log delay note in order timeline |
| `agent-returning` | Parcel return is initiated and in-progress | Mark order as "Returning" |
| `returned` | Parcel returned back to merchant warehouse | Restock inventory, refund if prepaid |
| `agent-area-change` | Customer requested an area change, re-routing in progress | Update destination log |
| `paid` | Cash collection amount settled to merchant account | Reconcile financial ledger |

---

## 🚚 Delivery Type Reference Table

| Delivery Type | Meaning | Operational Context |
| :--- | :--- | :--- |
| `regular` | Regular forward delivery | Standard forward shipment from merchant to customer. |
| `reverse` | Regular reverse delivery | Direct return pickup from customer to merchant. |
| `exchange-delivery` | Forward exchange parcel | Delivery of a replacement product to the customer. |
| `exchange-return` | Reverse exchange parcel | Simultaneous pickup of the exchanged item back to merchant. |
| `partial-delivery` | Partial delivery parcel | Customer kept part of multi-item order. |
| `partial-return` | Partial return parcel | Remainder of rejected multi-item order returning. |

---

## 💻 Sample Webhook Handlers

### Node.js / Express Example

```javascript
const express = require('express');
const app = express();
app.use(express.json());

app.post('/api/webhooks/redx', async (req, res) => {
  const { token } = req.query;
  if (token !== process.env.REDX_WEBHOOK_SECRET) {
    return res.status(401).json({ error: 'Unauthorized secret token' });
  }

  const { tracking_number, status, invoice_number, timestamp } = req.body;
  console.log(`[RedX Webhook] Parcel ${tracking_number} changed to ${status}`);

  if (status === 'delivered') {
    // Update your database order to completed
  } else if (status === 'returned') {
    // Restock items
  }

  return res.status(200).json({ received: true });
});
```

### Laravel PHP Example

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedxWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->query('token') !== config('services.redx.webhook_secret')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $trackingNumber = $request->input('tracking_number');
        $status         = $request->input('status');
        $invoiceNumber  = $request->input('invoice_number');

        // Handle order state transition in DB
        // Order::where('invoice_id', $invoiceNumber)->update(['status' => $status]);

        return response()->json(['status' => 'acknowledged']);
    }
}
```
