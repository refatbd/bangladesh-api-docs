# 02. Parcels & Order Management

This guide covers full parcel lifecycle management on RedX Delivery, including creating new parcels, updating properties (such as customer address or status cancellation), and fetching comprehensive parcel records.

---

## 1. Create Parcel

Creates a new delivery consignment in the RedX network. Upon successful creation, RedX issues an alphanumeric unique `tracking_id`.

- **Method:** `POST`
- **Endpoint:** `/parcel`
- **Sandbox URL:** `https://sandbox.redx.com.bd/v1.0.0-beta/parcel`
- **Production URL:** `https://openapi.redx.com.bd/v1.0.0-beta/parcel`

### Request Headers

```http
API-ACCESS-TOKEN: Bearer <jwt_token>
Content-Type: application/json
```

### Request Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `customer_name` | `string` | **Yes** | Full name of the recipient customer. |
| `customer_phone` | `string` | **Yes** | Active 11-digit Bangladeshi mobile number (e.g. `01712345678`). |
| `delivery_area` | `string` | **Yes** | Exact name of the delivery area (obtained from `/areas`). |
| `delivery_area_id` | `integer` | **Yes** | Numeric area identifier (obtained from `/areas`). |
| `customer_address` | `string` | **Yes** | Full street address, road, flat, and house number. |
| `cash_collection_amount` | `string`/`number` | **Yes** | Amount in BDT to collect from recipient upon delivery. Pass `0` for prepaid orders. |
| `parcel_weight` | `string`/`number` | **Yes** | Weight of the shipment in grams (e.g. `500` for 0.5 kg). |
| `value` | `string`/`number` | **Yes** | Declared monetary value of the goods for transit insurance / compensation. |
| `merchant_invoice_id` | `string` | No | Your internal order ID or invoice number (e.g. `INV-2026-9041`). |
| `instruction` | `string` | No | Special handling instructions for the delivery rider. |
| `is_closed_box` | `boolean` | No | `true` = closed box (no unpacking before payment), `false` = customer can inspect. |
| `paid_return_charge` | `number` | No | Return shipping fee to charge recipient if rejected. Omit or pass `0` if not applicable. |
| `pickup_store_id` | `integer` | No | ID of your registered pickup warehouse. If omitted, uses your merchant default. |
| `parcel_details_json` | `array` | No | Breakdown of items inside the consignment. |

#### `parcel_details_json` Object Structure

| Field | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `name` | `string` | **Yes** | Item / SKU name (e.g., `Cotton Polo T-Shirt`). |
| `category` | `string` | **Yes** | Product classification (e.g., `Apparel`, `Electronics`). |
| `value` | `integer` | **Yes** | Declared item sub-value in BDT. |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/parcel' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' \
  --header 'Content-Type: application/json' \
  --data '{
      "customer_name": "Rahim Ahmed",
      "customer_phone": "01711223344",
      "delivery_area": "Mirpur DOHS",
      "delivery_area_id": 12,
      "customer_address": "House 45, Road 7, Avenue 3, Mirpur DOHS, Dhaka",
      "merchant_invoice_id": "INV-2026-8801",
      "cash_collection_amount": "1500",
      "paid_return_charge": 0,
      "parcel_weight": 750,
      "instruction": "Call recipient before dispatch",
      "value": 1500,
      "is_closed_box": true,
      "pickup_store_id": 1,
      "parcel_details_json": [
          {
              "name": "Bluetooth Headphones",
              "category": "Electronics",
              "value": 1500
          }
      ]
  }'
```

### Sample Response (`200 OK`)

```json
{
  "tracking_id": "21A427TU4BN3R"
}
```

---

## 2. Update Parcel (`PATCH /parcels`)

Allows merchants to dynamically modify parcel properties or cancel a booking prior to rider dispatch.

- **Method:** `PATCH`
- **Endpoint:** `/parcels`

### Request Parameters

| Field | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `entity_type` | `string` | **Yes** | Target entity type. Always set to `'parcel-tracking-id'`. |
| `entity_id` | `string` | **Yes** | The RedX `tracking_id` to update. |
| `update_details` | `object` | **Yes** | Container object holding property update directives. |
| `update_details.property_name` | `string` | **Yes** | Target attribute name (e.g., `status`, `delivery_address`). |
| `update_details.new_value` | `string` | **Yes** | Target value (e.g., `'cancelled'`). |
| `update_details.reason` | `string` | No | Audit note explaining the change. |

### Sample Request: Order Cancellation

```bash
curl --location --request PATCH 'https://sandbox.redx.com.bd/v1.0.0-beta/parcels' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' \
  --header 'Content-Type: application/json' \
  --data '{
      "entity_type": "parcel-tracking-id",
      "entity_id": "21A427TU4BN3R",
      "update_details": {
          "property_name": "status",
          "new_value": "cancelled",
          "reason": "Customer requested order cancellation"
      }
  }'
```

### Sample Response (`200 OK`)

```json
{
  "success": true,
  "message": "Request Accepted"
}
```

---

## 3. Get Parcel Details (`GET /parcel/info/:tracking_id`)

Retrieves the complete profile of an existing parcel, including delivery charges, COD amount, assigned rider area, pickup location, and creation timestamp.

- **Method:** `GET`
- **Endpoint:** `/parcel/info/<:tracking_id>`

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/parcel/info/21A427TU4BN3R' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

### Sample Response (`200 OK`)

```json
{
  "parcel": {
    "tracking_id": "21A427TU4BN3R",
    "customer_name": "Rahim Ahmed",
    "customer_phone": "01711223344",
    "customer_address": "House 45, Road 7, Avenue 3, Mirpur DOHS, Dhaka",
    "delivery_area": "Mirpur DOHS",
    "delivery_area_id": 12,
    "charge": 60,
    "cash_collection_amount": 1500,
    "parcel_weight": 750,
    "merchant_invoice_id": "INV-2026-8801",
    "status": "pickup-pending",
    "instruction": "Call recipient before dispatch",
    "created_at": "2026-04-27T08:29:14.000Z",
    "delivery_type": "regular",
    "value": "1500",
    "pickup_location": {
      "id": 1,
      "name": "Main Warehouse",
      "address": "Malibagh, Dhaka",
      "area_name": "Malibag",
      "area_id": 1
    }
  }
}
```
