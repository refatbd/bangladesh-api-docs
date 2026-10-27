# 05. Pickup Stores & Warehouse Management

RedX supports multi-warehouse inventory sourcing. Merchants can configure multiple dispatch locations (e.g., Central Warehouse, Retail Outlet, Factory) and assign specific orders to different pickup stores via `pickup_store_id`.

---

## 1. Create Pickup Store (`POST /pickup/store`)

Registers a new dispatch location with contact and geolocation details.

- **Method:** `POST`
- **Endpoint:** `/pickup/store`

### Request Headers

```http
API-ACCESS-TOKEN: Bearer <jwt_token>
Content-Type: application/json
```

### Request Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `name` | `string` | **Yes** | Human-readable title of the warehouse (e.g. `Tejgaon Central Hub`). |
| `phone` | `string` | **Yes** | Contact phone number for rider coordination during package handover. |
| `address` | `string` | **Yes** | Detailed street, floor, and building address. |
| `area_id` | `integer` | **Yes** | Delivery area identifier representing the warehouse location (from `/areas`). |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/pickup/store' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...' \
  --header 'Content-Type: application/json' \
  --data '{
      "name": "Dhanmondi Outlet Hub",
      "phone": "01898000999",
      "address": "House 12, Road 4, Dhanmondi, Dhaka",
      "area_id": 2
  }'
```

### Sample Response (`200 OK`)

```json
{
  "id": 1,
  "name": "Dhanmondi Outlet Hub",
  "address": "House 12, Road 4, Dhanmondi, Dhaka",
  "area_name": "Dhanmondi",
  "area_id": 2,
  "phone": "01898000999"
}
```

---

## 2. Get All Pickup Stores (`GET /pickup/stores`)

Lists all registered merchant warehouses along with their creation timestamps and active phone numbers.

- **Method:** `GET`
- **Endpoint:** `/pickup/stores`

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/pickup/stores' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

### Sample Response (`200 OK`)

```json
{
  "pickup_stores": [
    {
      "id": 1,
      "name": "Dhanmondi Outlet Hub",
      "address": "House 12, Road 4, Dhanmondi, Dhaka",
      "area_name": "Dhanmondi",
      "area_id": 2,
      "phone": "01898000999",
      "created_at": "2026-01-15T09:30:00.000Z"
    },
    {
      "id": 2,
      "name": "Chattogram Depot",
      "address": "Agrabad Commercial Area",
      "area_name": "Agrabad",
      "area_id": 45,
      "phone": "01700112233",
      "created_at": "2026-03-10T14:22:18.000Z"
    }
  ]
}
```

---

## 3. Pickup Store Details (`GET /pickup/store/info/:id`)

Fetches metadata for a single specific warehouse by its numeric identifier.

- **Method:** `GET`
- **Endpoint:** `/pickup/store/info/<:pickup_store_id>`

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/pickup/store/info/1' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

### Sample Response (`200 OK`)

```json
{
  "pickup_store": {
    "id": 1,
    "name": "Dhanmondi Outlet Hub",
    "address": "House 12, Road 4, Dhanmondi, Dhaka",
    "area_name": "Dhanmondi",
    "area_id": 2,
    "phone": "01898000999",
    "created_at": "2026-01-15T09:30:00.000Z"
  }
}
```
