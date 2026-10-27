# 04. Order Placement (General & Exchange)

The `/order-place` endpoint registers single parcel consignments for pickup, line-haul sorting, and last-mile doorstep delivery.

---

## 1. Endpoint Overview

- **Method**: `POST`
- **Path**: `/order-place`
- **Headers**:
  ```http
  API-KEY: YOUR_MERCHANT_API_KEY
  API-SECRET: YOUR_MERCHANT_API_SECRET
  USER-ID: YOUR_MERCHANT_USER_ID
  Content-Type: application/json
  ```

---

## 2. Parameter Reference Table

| Field Name | Type | Mandatory? | Constraints & Details | Example |
| :--- | :---: | :---: | :--- | :--- |
| `recipient_name` | `string` | **Yes** | Max 250 characters | `"Tanvir Ahmed"` |
| `recipient_mobile` | `string` | **Yes** | Exactly 11 digits starting with 01 | `"01811223344"` |
| `recipient_city` | `string` | **Yes** | Valid city from `/city-list` (Max 40 chars) | `"Dhaka"` |
| `recipient_thana` | `string` | **Yes** | Valid thana from `/thana-list` (Max 40 chars) | `"Badda"` |
| `recipient_area` | `string` | **Yes** | Valid area from `/area-list` (Max 40 chars) | `"Middle Badda"` |
| `recipient_address` | `string` | **Yes** | Detailed street & house address (Max 40 chars) | `"House 14, Road 3, Block D"` |
| `recipient_zip` | `string` | **Yes** | 4-digit postal code | `"1212"` |
| `package_code` | `string` | **Yes** | Contracted package code from `/packages` | `"#2431"` |
| `product_price` | `integer` | **Yes** | COD amount to collect from recipient in BDT | `1500` |
| `payment_method` | `string` | **Yes** | Allowed: `COD`, `POS`, `MPAY`, `CCRD` | `"COD"` |
| `parcel_type` | `string` | No | `BOX` (default) or `DOC` | `"BOX"` |
| `requested_delivery_time`| `string` | No | Date format `YYYY-MM-DD` | `"2026-10-28"` |
| `delivery_hour` | `string` | No | Time slot (e.g. `any`, `morning`, `evening`) | `"any"` |
| `recipient_landmark` | `string` | No | Prominent landmark | `"Near DBBL ATM"` |
| `product_id` | `string` | No | Merchant internal invoice / SKU ID (Max 20 chars) | `"INV-2026-001"` |
| `pick_address` | `string` | No | Alternative pickup address (Max 255 chars) | `"Warehouse 3, Tejgaon"` |
| `pick_hub` | `integer` | No | Branch hub ID from `/branch-list` | `18490` |
| `comments` | `string` | No | Delivery rider instructions (Max 255 chars) | `"Please call before delivery"` |
| `number_of_item` | `integer` | No | Total item quantity (default `1`) | `2` |
| `actual_product_price` | `integer` | No | Actual merchandise retail value (default `0`) | `1200` |
| `is_fragile` | `integer` | No | `0` = Standard (default), `1` = Fragile / Liquid | `1` |
| `sending_type` | `integer` | No | `1` = General Delivery (default), `2` = Exchange | `1` |
| `is_ipay` | `integer` | No | `1` = Instant Pay COD settlement, `0` = Standard | `0` |
| `hub_delivery` | `string` | No | `"yes"` if customer picks up from branch hub | `null` |
| `hub_delivery_id` | `integer` | No | Destination hub ID (Required if `hub_delivery="yes"`) | `18490` |

---

## 3. Order Types & Variations

### A. Standard COD Parcel Order

```json
{
  "recipient_name": "Tanvir Ahmed",
  "recipient_mobile": "01811223344",
  "recipient_city": "Dhaka",
  "recipient_thana": "Badda",
  "recipient_area": "Middle Badda",
  "recipient_address": "House 14, Road 3, Block D",
  "recipient_zip": "1212",
  "package_code": "#2431",
  "product_price": 1850,
  "payment_method": "COD",
  "parcel_type": "BOX",
  "product_id": "INV-2026-901",
  "comments": "Fragile electronic accessory",
  "is_fragile": 1,
  "sending_type": 1,
  "is_ipay": 0
}
```

### B. Exchange Delivery Order (`sending_type: 2`)

For customer product replacement (e.g. clothing size swap):
```json
{
  "recipient_name": "Rafiqul Islam",
  "recipient_mobile": "01700112233",
  "recipient_city": "Dhaka",
  "recipient_thana": "Dhanmondi",
  "recipient_area": "Dhanmondi 27",
  "recipient_address": "House 45, Road 27",
  "recipient_zip": "1209",
  "package_code": "#2431",
  "product_price": 100,
  "payment_method": "COD",
  "sending_type": 2,
  "comments": "Size swap: Hand over Size XL and collect Size M"
}
```

---

## 4. Response Schemas

### Successful Booking (`200 OK`)

```json
{
  "errors": [],
  "response_code": 200,
  "message": "Order Submitted",
  "ID": "ECR1030102170392"
}
```
*The returned `ID` (e.g. `ECR1030102170392`) is your primary tracking identifier across all eCourier systems.*

### Validation Failure (`400 Bad Request`)

```json
{
  "errors": [
    "Invalid Request",
    "recipient_name Parameter either missing or not set correctly. "
  ],
  "response_code": 400
}
```

### COD Zero Error (`400 Bad Request`)
If `payment_method` is `COD` or `BOD` (bKash on delivery) but `product_price` is `0`:
```json
{
  "errors": [
    "Invalid Request",
    "Product price 0 not allow in BOD and COD payment method"
  ],
  "response_code": 400
}
```
*(For prepaid orders with zero COD collection, select payment method `CCRD` or `MPAY`).*
