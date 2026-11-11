# 03. Stores & Warehouse Management

Carrybee organizes logistics operations around **Merchant Stores**. A store represents a physical warehouse, outlet, or distribution center where delivery riders report for package pickup.

---

## 1. Create a Pickup Store (`POST /api/v2/stores`)

Registers a new warehouse or branch location under your merchant profile.

- **Method:** `POST`
- **Endpoint:** `/api/v2/stores`

### Request Parameters

| Parameter | Type | Required | Constraints / Description |
| :--- | :---: | :---: | :--- |
| `name` | `string` | **Yes** | Store name (Between 3 and 30 characters). |
| `contact_person_name` | `string` | **Yes** | Warehouse manager or dispatch lead (3–30 chars). |
| `contact_person_number` | `string` | **Yes** | Active 11-digit mobile phone number. |
| `contact_person_secondary_number` | `string` | No | Secondary emergency contact phone number. |
| `address` | `string` | **Yes** | Detailed street & building address (3–100 chars). |
| `city_id` | `integer` | **Yes** | Destination `city_id` (from `/api/v2/cities`). |
| `zone_id` | `integer` | **Yes** | Destination `zone_id` (from `/zones`). |
| `area_id` | `integer` | **Yes** | Destination `area_id` (from `/areas`). |
| `lat` | `number` | No | Latitude coordinate for GPS pinpointing. |
| `lng` | `number` | No | Longitude coordinate for GPS pinpointing. |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.carrybee.com/api/v2/stores' \
  --header 'Client-ID: <client-id>' \
  --header 'Client-Secret: <client-secret>' \
  --header 'Client-Context: <client-context>' \
  --header 'Content-Type: application/json' \
  --data '{
    "name": "Gulshan Central Depot",
    "contact_person_name": "Rahim Uddin",
    "contact_person_number": "01711000000",
    "address": "House 1, Road 2, Gulshan 1, Dhaka",
    "city_id": 14,
    "zone_id": 10,
    "area_id": 100
  }'
```

### Sample Response (`201 Created`)

```json
{
  "error": false,
  "message": "Store created successfully",
  "data": null
}
```

---

## 2. List Merchant Stores (`GET /api/v2/stores`)

Fetches all configured pickup locations along with their approval status and default flags.

- **Method:** `GET`
- **Endpoint:** `/api/v2/stores`

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "Store list",
  "data": {
    "stores": [
      {
        "id": "str_9921ab4c",
        "name": "Gulshan Central Depot",
        "contact_person_name": "Rahim Uddin",
        "contact_person_number": "01711000000",
        "address": "House 1, Road 2, Gulshan 1, Dhaka",
        "city_id": 14,
        "zone_id": 10,
        "area_id": 100,
        "is_active": true,
        "is_approved": true,
        "is_default_pickup_store": true,
        "is_default_return_store": true,
        "pending_count": 0
      }
    ]
  }
}
```

### Key Field Reference

- `id`: Store identifier string. **Save this ID to pass as `store_id` when placing orders.**
- `is_approved`: Must be `true` before parcels can be assigned to this store.
- `pending_count`: Number of booked parcels currently awaiting rider collection.
