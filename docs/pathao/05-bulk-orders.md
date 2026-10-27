# 05. Bulk Consignment Orders

For high-volume merchants, e-commerce storefronts, and automated warehouse packing pipelines, Pathao provides an asynchronous **Bulk Order Creation** endpoint capable of accepting an array of order objects in a single HTTP payload.

---

## Create Bulk Orders (`POST /aladdin/api/v1/orders/bulk`)

- **Method:** `POST`
- **Endpoint:** `/aladdin/api/v1/orders/bulk`

### Request Headers

```http
Authorization: Bearer <access_token>
Content-Type: application/json; charset=UTF-8
Accept: application/json
```

---

## Request Payload Structure

The root payload requires an `orders` array containing standard Order objects.

```json
{
  "orders": [
    {
      "store_id": 4821,
      "merchant_order_id": "ORD-1001",
      "recipient_name": "Arif Chowdhury",
      "recipient_phone": "01711000111",
      "recipient_address": "House 12, Road 4, Sector 10, Uttara, Dhaka",
      "delivery_type": 48,
      "item_type": 2,
      "special_instruction": "Fragile items inside",
      "item_quantity": 2,
      "item_weight": 0.5,
      "amount_to_collect": 1500,
      "item_description": "2x Polo Shirts"
    },
    {
      "store_id": 4821,
      "merchant_order_id": "ORD-1002",
      "recipient_name": "Nafis Imtiaz",
      "recipient_phone": "01811000222",
      "recipient_address": "Flat 4B, House 5, Dhanmondi 27, Dhaka",
      "delivery_type": 48,
      "item_type": 2,
      "special_instruction": "Deliver after 3 PM",
      "item_quantity": 1,
      "item_weight": 1.0,
      "amount_to_collect": 2200,
      "item_description": "Leather Backpack"
    }
  ]
}
```

---

## Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/orders/bulk' \
  --header 'Content-Type: application/json; charset=UTF-8' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  --data '{
    "orders": [
      {
        "store_id": 4821,
        "merchant_order_id": "ORD-BULK-01",
        "recipient_name": "Customer One",
        "recipient_phone": "01711223344",
        "recipient_address": "Uttara Sector 4, Dhaka",
        "delivery_type": 48,
        "item_type": 2,
        "item_quantity": 1,
        "item_weight": 0.5,
        "amount_to_collect": 500
      }
    ]
  }'
```

---

## Asynchronous Response (`202 Accepted`)

Because bulk processing runs in the background queue to avoid gateway timeout, Pathao responds with an **HTTP 202 Accepted**:

```json
{
  "message": "Your bulk order creation request is accepted,<br>  please wait some time to complete order creation.",
  "type": "success",
  "code": 202,
  "data": true
}
```

### Production Recommendations

1. **Chunking Size**: Limit batch sizes to **25–50 orders per HTTP request** to prevent memory bottlenecks or network socket drops.
2. **Reconciliation**: Provide unique `merchant_order_id` values on every item in the array to easily match consignments created in Pathao with your database.
