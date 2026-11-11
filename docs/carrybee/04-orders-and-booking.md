# 04. Orders, Consignments & Tracking

This guide details how to create single or bulk parcel orders on Carrybee, query real-time delivery progress, and execute order cancellations.

---

## 1. Create a Single Order (`POST /api/v2/orders`)

- **Method:** `POST`
- **Endpoint:** `/api/v2/orders`

### Request Parameters

| Parameter | Type | Required | Constraints / Description |
| :--- | :---: | :---: | :--- |
| `store_id` | `string` | **Yes** | Warehouse ID where rider collects package (from `GET /stores`). |
| `merchant_order_id` | `string` | No | Your internal order invoice ID (< 50 chars). |
| `delivery_type` | `integer` | **Yes** | **`1`** = Normal Delivery, **`2`** = Express Delivery. |
| `product_type` | `integer` | **Yes** | **`1`** = Parcel, **`2`** = Book, **`3`** = Document. |
| `recipient_phone` | `string` | **Yes** | 11-digit mobile phone number (e.g. `01800000000`). |
| `recipient_secendary_phone` | `string` | No | Secondary contact phone number. |
| `recipient_name` | `string` | **Yes** | Recipient full name (Between 2 and 99 characters). |
| `recipient_address` | `string` | **Yes** | Full delivery address (10–200 characters). |
| `city_id` | `integer` | No | Destination `city_id` (from location API). |
| `zone_id` | `integer` | No | Destination `zone_id`. |
| `area_id` | `integer` | No | Destination `area_id`. |
| `item_weight` | `integer` | **Yes** | Weight in **grams** (Between 1 and 25,000 g). |
| `collectable_amount` | `integer` | No | Cash-on-Delivery amount in BDT (0–100,000). Pass `0` for prepaid. |
| `item_quantity` | `integer` | No | Item count inside parcel (1–200). Default `1`. |
| `product_description`| `string` | No | Summary of package contents (< 255 chars). |
| `special_instruction`| `string` | No | Specific delivery instructions (< 255 chars). |
| `is_closed_box` | `boolean` | No | `true` = customer cannot inspect before payment. Default `false`. |
| `is_exchange` | `boolean` | No | `true` if this order involves an exchange handover. Default `false`. |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.carrybee.com/api/v2/orders' \
  --header 'Client-ID: <client-id>' \
  --header 'Client-Secret: <client-secret>' \
  --header 'Client-Context: <client-context>' \
  --header 'Content-Type: application/json' \
  --data '{
    "store_id": "str_9921ab4c",
    "merchant_order_id": "INV-2026-9041",
    "delivery_type": 1,
    "product_type": 1,
    "recipient_name": "Karim Rahman",
    "recipient_phone": "01811223344",
    "recipient_address": "House 5, Road 3, Banani, Dhaka",
    "city_id": 14,
    "zone_id": 10,
    "item_weight": 500,
    "collectable_amount": 1500,
    "is_closed_box": true
  }'
```

### Sample Response (`201 Created`)

```json
{
  "error": false,
  "message": "Order created successfully",
  "data": {
    "order": {
      "consignment_id": "FX1212124433",
      "store_id": "str_9921ab4c",
      "merchant_order_id": "INV-2026-9041",
      "collectable_amount": "1500",
      "cod_fee": 15,
      "delivery_fee": "83.46"
    }
  }
}
```

---

## 2. Bulk Orders Creation (`POST /api/v2/orders-bulk`)

Dispatches an array of order objects for asynchronous background batch processing.

- **Method:** `POST`
- **Endpoint:** `/api/v2/orders-bulk`
- **Response:** `202 Accepted`

```json
[
  {
    "store_id": "str_9921ab4c",
    "merchant_order_id": "bulk-01",
    "delivery_type": 1,
    "product_type": 1,
    "recipient_phone": "01811000111",
    "recipient_name": "Customer 1",
    "recipient_address": "Banani, Dhaka",
    "city_id": 14,
    "zone_id": 10,
    "item_weight": 500,
    "collectable_amount": 1200
  }
]
```

---

## 3. Get Order Details & Tracking (`GET /api/v2/orders/:consignment_id/details`)

Fetches complete lifecycle status, rider attempt counts, and settlement invoice references. Accepts either Carrybee's `consignment_id` or your `merchant_order_id`.

- **Method:** `GET`
- **Endpoint:** `/api/v2/orders/<:consignment_id>/details`

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "Order details",
  "data": {
    "transfer_status": "delivered",
    "store_id": "str_9921ab4c",
    "consignment_id": "FX1212124433",
    "merchant_order_id": "INV-2026-9041",
    "recipient_name": "Karim Rahman",
    "recipient_phone": "01811223344",
    "recipient_address": "House 5, Road 3, Banani",
    "collectable_amount": "1500",
    "collected_amount": "1500",
    "cod_fee": 15,
    "delivery_fee": "83.46",
    "attempt": 1,
    "reason": "",
    "invoice_id": "INV-CB-48912",
    "payment_status": "paid",
    "updated_at": "2026-04-12T14:20:00+00:00"
  }
}
```

---

## 4. Cancel Order (`POST /api/v2/orders/:consignment_id/cancel`)

Cancels an order before courier dispatch.

- **Method:** `POST`
- **Endpoint:** `/api/v2/orders/<:consignment_id>/cancel`

```json
{
  "cancellation_reason": "Customer cancelled order before dispatch"
}
```

### Sample Response (`202 Accepted`)

```json
{
  "error": false,
  "message": "Cancellation confirmed",
  "data": null
}
```
