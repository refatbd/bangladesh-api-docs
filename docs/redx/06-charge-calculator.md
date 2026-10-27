# 06. Charge & Delivery Cost Calculator

The Charge Calculator endpoint allows merchants to quote exact shipping fees, Cash-on-Delivery (COD) commission rates, and total fulfillment costs to customers in real time during checkout before an order is placed.

---

## Calculate Parcel Charge (`GET /charge/charge_calculator`)

Estimates delivery charges based on origin pickup area, destination delivery area, parcel weight, and COD collection amount.

- **Method:** `GET`
- **Endpoint:** `/charge/charge_calculator`
- **Sandbox URL:** `https://sandbox.redx.com.bd/v1.0.0-beta/charge/charge_calculator`
- **Production URL:** `https://openapi.redx.com.bd/v1.0.0-beta/charge/charge_calculator`

### Request Headers

```http
API-ACCESS-TOKEN: Bearer <jwt_token>
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `delivery_area_id` | `number` | **Yes** | Destination area identifier (from `/areas`). |
| `pickup_area_id` | `number` | **Yes** | Origin warehouse area identifier (from `/areas`). |
| `cash_collection_amount` | `number` | **Yes** | Total cash to collect on delivery in BDT (`0` if prepaid). |
| `weight` | `number` | **Yes** | Weight of the shipment in **grams** (e.g. `1000` = 1 kg). |

---

## Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/charge/charge_calculator?delivery_area_id=12&pickup_area_id=1&cash_collection_amount=1500&weight=750' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

---

## Sample Response (`200 OK`)

```json
{
  "deliveryCharge": 60,
  "codCharge": 15
}
```

### Response Schema

| Field Name | Type | Description |
| :--- | :---: | :--- |
| `deliveryCharge` | `number` | Base shipping freight fee in BDT based on weight slab and inter-city / inside-Dhaka routing. |
| `codCharge` | `number` | Cash-on-Delivery processing percentage fee (typically 0% inside Dhaka, 1% outside Dhaka). |

### 🧮 Total Merchant Calculation Formula

```text
Total Shipping Deduction = deliveryCharge + codCharge
Net Merchant Payable     = cash_collection_amount - Total Shipping Deduction
```
