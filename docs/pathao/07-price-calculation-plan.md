# 07. Price Calculation & Merchant Plan API

Pathao allows e-commerce checkouts to dynamically compute delivery charges, promotional discounts, and Cash-on-Delivery (COD) fees before an order is placed, factoring in destination geography, parcel weight, and delivery urgency.

---

## Price Calculation API (`POST /aladdin/api/v1/merchant/price-plan`)

- **Method:** `POST`
- **Endpoint:** `/aladdin/api/v1/merchant/price-plan`

### Request Headers

```http
Authorization: Bearer <access_token>
Content-Type: application/json; charset=UTF-8
Accept: application/json
```

---

## Request Parameters

| Field Name | Type | Required | Constraints / Description |
| :--- | :---: | :---: | :--- |
| `store_id` | `integer` | **Yes** | Origin warehouse ID (pickup point determines routing zone). |
| `item_type` | `integer` | **Yes** | `1` for Document, `2` for Parcel. |
| `delivery_type` | `integer` | **Yes** | `48` for Normal Next-Day, `12` for On-Demand Express. |
| `item_weight` | `float` | **Yes** | Parcel weight in kg (Minimum `0.5`, Maximum `10.0`). |
| `recipient_city` | `integer` | **Yes** | Destination `city_id` (from `/city-list`). |
| `recipient_zone` | `integer` | **Yes** | Destination `zone_id` (from `/cities/:id/zone-list`). |

---

## Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/merchant/price-plan' \
  --header 'Content-Type: application/json; charset=UTF-8' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  --data '{
    "store_id": 4821,
    "item_type": 2,
    "delivery_type": 48,
    "item_weight": 0.5,
    "recipient_city": 1,
    "recipient_zone": 1070
  }'
```

---

## Sample Response (`200 OK`)

```json
{
  "message": "price",
  "type": "success",
  "code": 200,
  "data": {
    "price": 80,
    "discount": 0,
    "promo_discount": 0,
    "plan_id": 69,
    "cod_enabled": 1,
    "cod_percentage": 0.01,
    "additional_charge": 0,
    "final_price": 80
  }
}
```

### Breakdown of Cost Variables

| Field Name | Type | Description |
| :--- | :---: | :--- |
| `price` | `integer` | Standard base delivery tariff for the selected weight and route. |
| `discount` | `integer` | Tiered merchant volume discount in BDT. |
| `promo_discount`| `integer` | Any active campaign promotional discount applied. |
| `plan_id` | `integer` | Pathao internal billing plan rate card ID. |
| `cod_enabled` | `integer` | `1` indicates Cash-on-Delivery is supported in this zone; `0` indicates prepaid only. |
| `cod_percentage`| `float` | COD handling fee percentage (e.g. `0.01` represents 1% commission). |
| `additional_charge`| `integer` | Extra handling fee for out-of-coverage or special zones. |
| `final_price` | `number` | Net delivery fee payable by merchant (`(price - discount - promo) + additional_charge`). |

---

## 💡 Practical Customer Checkout Calculation

```text
Item Subtotal        = 1,500 BDT
Calculated Shipping  = final_price (e.g. 80 BDT)
COD Surcharge (if 1%)= 1,500 * 0.01 = 15 BDT
Total Recipient Due  = 1,500 BDT (Collected COD)
Net Merchant Payout  = 1,500 - (80 + 15) = 1,405 BDT
```
