# 05. Reverse Pickup & Exchange Workflows

Carrybee provides first-class support for e-commerce return logistics and product exchanges through three dedicated endpoints.

---

## 1. Standalone Reverse Pickup (`POST /api/v2/orders/reverse-pickup`)

Creates an independent reverse pickup consignment to collect a package from a customer and return it directly to your warehouse.

- **Method:** `POST`
- **Endpoint:** `/api/v2/orders/reverse-pickup`

### Request Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `store_id` | `string` | **Yes** | Destination warehouse where return will be deposited. |
| `merchant_order_id` | `string` | No | Internal tracking ID. |
| `product_type` | `integer` | **Yes** | 1 = Parcel, 2 = Book, 3 = Document. |
| `customer_name` | `string` | **Yes** | Customer name from whom parcel is collected. |
| `customer_phone` | `string` | **Yes** | Customer 11-digit mobile number. |
| `customer_address` | `string` | **Yes** | Customer pickup address. |
| `city_id` | `integer` | **Yes** | Customer pickup `city_id`. |
| `zone_id` | `integer` | **Yes** | Customer pickup `zone_id`. |
| `item_weight` | `integer` | **Yes** | Weight in grams (1–25,000 g). |

### Sample Response (`201 Created`)

```json
{
  "error": false,
  "message": "Reverse pickup created",
  "data": {
    "consignment_id": "RP1212124433",
    "store_id": "str_9921ab4c",
    "merchant_order_id": "RET-01",
    "delivery_fee": "80.00"
  }
}
```

---

## 2. Reverse Pickup from Delivered Order (`POST /api/v2/orders/:id/reverse-pickup`)

Initiates a return directly from a previously delivered consignment. All omitted customer details (`customer_name`, `customer_phone`, `address`, `city_id`, `zone_id`) automatically fall back to the reference order values.

- **Method:** `POST`
- **Endpoint:** `/api/v2/orders/<:consignment_id>/reverse-pickup`

```json
{
  "item_weight": 500,
  "product_description": "Defective item return"
}
```

---

## 3. Order Exchange (`POST /api/v2/orders/:id/exchange`)

Creates an exchange delivery from a delivered consignment. The rider simultaneously delivers replacement merchandise and collects the returned product.

- **Method:** `POST`
- **Endpoint:** `/api/v2/orders/<:consignment_id>/exchange`

### Request Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `consignment_id` | `string` | **Yes** | Original delivered consignment ID in path. |
| `collectable_amount` | `integer` | No | Additional COD amount to collect (0–100,000 BDT). |
| `item_weight` | `integer` | No | Replacement parcel weight in grams. |
| `product_description`| `string` | No | Description of exchange merchandise. |

### Sample Response (`201 Created`)

```json
{
  "error": false,
  "message": "Exchange order created",
  "data": {
    "order": {
      "consignment_id": "EX1212124433",
      "store_id": "str_9921ab4c",
      "merchant_order_id": "EXC-9041",
      "collectable_amount": "200",
      "cod_fee": 2,
      "delivery_fee": "83.46"
    }
  }
}
```

> [!NOTE]
> Exchange orders created via this endpoint automatically receive a consignment ID prefixed with **`EX`** (e.g. `EX1212124433`).
