# 04. Creating Orders & Consignments

This guide details how to book single delivery orders on Pathao Courier. Upon placement, Pathao creates an active consignment and returns a unique `consignment_id`.

---

## Create a New Order (`POST /aladdin/api/v1/orders`)

- **Method:** `POST`
- **Endpoint:** `/aladdin/api/v1/orders`
- **Sandbox URL:** `https://courier-api-sandbox.pathao.com/aladdin/api/v1/orders`
- **Production URL:** `https://api-hermes.pathao.com/aladdin/api/v1/orders`

### Request Headers

```http
Authorization: Bearer <access_token>
Content-Type: application/json
Accept: application/json
```

---

## Request Parameters & Constraints

| Parameter | Type | Required | Constraints / Description |
| :--- | :---: | :---: | :--- |
| `store_id` | `integer` | **Yes** | Warehouse ID where rider collects parcel (from `GET /stores`). |
| `merchant_order_id` | `string` | No | Your internal order ID / invoice reference (e.g. `ORD-8821`). |
| `recipient_name` | `string` | **Yes** | Recipient full name (Between 3 and 100 characters). |
| `recipient_phone` | `string` | **Yes** | Exactly 11-digit Bangladeshi mobile number (`01XXXXXXXXX`). |
| `recipient_secondary_phone`| `string` | No | Alternative mobile number (11 digits). |
| `recipient_address` | `string` | **Yes** | Full delivery address (Between 10 and 220 characters). |
| `recipient_city` | `integer` | No | Receiver's `city_id`. If omitted, Pathao resolves from address. |
| `recipient_zone` | `integer` | No | Receiver's `zone_id`. |
| `recipient_area` | `integer` | No | Receiver's `area_id`. |
| `delivery_type` | `integer` | **Yes** | **`48`** for Standard Normal Delivery (Next Day).<br>**`12`** for On-Demand Express Delivery. |
| `item_type` | `integer` | **Yes** | **`1`** for Document / Envelope.<br>**`2`** for Parcel / Package. |
| `item_quantity` | `integer` | **Yes** | Total count of items inside package (minimum `1`). |
| `item_weight` | `float` | **Yes** | Weight in Kilograms (**Minimum `0.5` kg, Maximum `10.0` kg**). |
| `amount_to_collect` | `integer` | **Yes** | Amount in BDT to collect upon delivery. Pass `0` for prepaid. |
| `item_description` | `string` | No | Product description or contents notes. |
| `special_instruction` | `string` | No | Custom rider delivery notes (e.g., `Deliver before 5 PM`). |

---

## Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/orders' \
  --header 'Content-Type: application/json' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  --data '{
    "store_id": 4821,
    "merchant_order_id": "INV-2026-9041",
    "recipient_name": "Tariqul Islam",
    "recipient_phone": "01711223344",
    "recipient_secondary_phone": "01511223344",
    "recipient_address": "House 14, Road 7, Sector 3, Uttara, Dhaka-1230",
    "recipient_city": 1,
    "recipient_zone": 1070,
    "recipient_area": 37,
    "delivery_type": 48,
    "item_type": 2,
    "special_instruction": "Call before arrival",
    "item_quantity": 1,
    "item_weight": 0.5,
    "item_description": "Mens Polo Shirt",
    "amount_to_collect": 1200
  }'
```

---

## Sample Response (`200 OK`)

```json
{
  "message": "Order Created Successfully",
  "type": "success",
  "code": 200,
  "data": {
    "consignment_id": "PT9041A4B89",
    "merchant_order_id": "INV-2026-9041",
    "order_status": "Pending",
    "delivery_fee": 80
  }
}
```

### Key Returned Values

- `consignment_id`: Pathao's primary tracking key. Store this in your database to query tracking updates.
- `delivery_fee`: The calculated shipping fee deducted from merchant COD payout.
- `order_status`: Defaults to `Pending` until a rider is assigned for pickup.
