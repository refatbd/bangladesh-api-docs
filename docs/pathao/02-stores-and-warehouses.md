# 02. Stores & Warehouse Management

In Pathao Courier, an **Order** is inextricably linked to a **Store**. The `store_id` dictates the physical warehouse or pickup location where Pathao riders arrive to collect packages.

---

## 1. Create a New Store (`POST /aladdin/api/v1/stores`)

Registers a new dispatch warehouse or retail outlet under your merchant profile.

- **Method:** `POST`
- **Endpoint:** `/aladdin/api/v1/stores`

### Request Headers

```http
Authorization: Bearer <access_token>
Content-Type: application/json
Accept: application/json
```

### Request Parameters & Validation Rules

| Field Name | Type | Required | Constraints / Description |
| :--- | :---: | :---: | :--- |
| `name` | `string` | **Yes** | Store title (Between 3 and 50 characters). |
| `contact_name` | `string` | **Yes** | Store manager or dispatch handler (3 to 50 characters). |
| `contact_number` | `string` | **Yes** | Exactly 11-digit mobile number (e.g. `01711223344`). |
| `secondary_contact` | `string` | No | Secondary contact phone number (11 digits). |
| `otp_number` | `string` | No | Number designated to receive delivery OTP notifications. |
| `address` | `string` | **Yes** | Physical warehouse address (15 to 120 characters). |
| `city_id` | `integer` | **Yes** | City identifier (from `/city-list`). |
| `zone_id` | `integer` | **Yes** | Zone identifier (from `/cities/:id/zone-list`). |
| `area_id` | `integer` | **Yes** | Area identifier (from `/zones/:id/area-list`). |

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/stores' \
  --header 'Content-Type: application/json' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN' \
  --data '{
    "name": "Uttara Flagship Warehouse",
    "contact_name": "Rafiqul Islam",
    "contact_number": "01711223344",
    "secondary_contact": "01911223344",
    "otp_number": "01711223344",
    "address": "House 12, Road 4, Sector 10, Uttara, Dhaka-1230",
    "city_id": 1,
    "zone_id": 1070,
    "area_id": 37
  }'
```

### Sample Response (`200 OK`)

```json
{
  "message": "Store created successfully, Please wait one hour for approval.",
  "type": "success",
  "code": 200,
  "data": {
    "store_name": "Uttara Flagship Warehouse"
  }
}
```

> [!NOTE]
> Newly created stores require manual verification by the Pathao operations team. Store activation typically takes up to one hour.

---

## 2. Get Merchant Stores (`GET /aladdin/api/v1/stores`)

Fetches all configured stores associated with your merchant account.

- **Method:** `GET`
- **Endpoint:** `/aladdin/api/v1/stores`

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/stores' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

### Sample Response (`200 OK`)

```json
{
  "message": "Store list fetched.",
  "type": "success",
  "code": 200,
  "data": {
    "data": [
      {
        "store_id": 4821,
        "store_name": "Uttara Flagship Warehouse",
        "store_address": "House 12, Road 4, Sector 10, Uttara, Dhaka-1230",
        "is_active": 1,
        "city_id": 1,
        "zone_id": 1070,
        "hub_id": 15,
        "is_default_store": 1,
        "is_default_return_store": 1
      }
    ],
    "total": 1,
    "current_page": 1,
    "per_page": 1000,
    "last_page": 1
  }
}
```

### Response Field Descriptions

| Field | Type | Description |
| :--- | :---: | :--- |
| `store_id` | `integer` | **Crucial ID.** Pass this as `store_id` when placing parcel orders. |
| `is_active` | `integer` | `1` indicates active store available for dispatch; `0` indicates deactivated. |
| `is_default_store` | `integer` | `1` if this is the primary dispatch origin. |
| `is_default_return_store` | `integer` | `1` if rejected parcels are returned to this warehouse. |
