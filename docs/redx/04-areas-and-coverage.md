# 04. Areas & Delivery Coverage

RedX covers all 64 districts in Bangladesh through a hierarchical location model: **Division &rarr; District &rarr; Zone &rarr; Delivery Area**. Every parcel requires a valid `delivery_area_id` and matching `delivery_area` string.

RedX provides three distinct query endpoints to discover and cache area identifiers.

---

## 1. Get All Areas (`GET /areas`)

Fetches the complete directory of deliverable locations nationwide. Due to the response size (hundreds of zones), merchants should **cache this list locally** for 24–48 hours.

- **Method:** `GET`
- **Endpoint:** `/areas`

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/areas' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

### Sample Response (`200 OK`)

```json
{
  "areas": [
    {
      "id": 1,
      "name": "Mohammadpur(Dhaka)",
      "post_code": 1207,
      "division_name": "Dhaka",
      "zone_id": 1
    },
    {
      "id": 2,
      "name": "Dhanmondi",
      "post_code": 1209,
      "division_name": "Dhaka",
      "zone_id": 1
    },
    {
      "id": 12,
      "name": "Mirpur DOHS",
      "post_code": 1216,
      "division_name": "Dhaka",
      "zone_id": 1
    }
  ]
}
```

---

## 2. Get Areas By Postal Code (`GET /areas?post_code=...`)

Quickly filters available sub-areas and delivery hubs matching a recipient's 4-digit postal code.

- **Method:** `GET`
- **Endpoint:** `/areas?post_code=<:postal_code>`

### Query Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `post_code` | `integer` | **Yes** | 4-digit BD postal code (e.g. `1206` for Dhaka Cantonment / Mirpur). |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/areas?post_code=1206' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

### Sample Response (`200 OK`)

```json
{
  "areas": [
    {
      "id": 13,
      "name": "Kochukhet",
      "post_code": 1206,
      "division_name": "Dhaka",
      "zone_id": 1
    },
    {
      "id": 20,
      "name": "Ibrahimpur",
      "post_code": 1206,
      "division_name": "Dhaka",
      "zone_id": 1
    },
    {
      "id": 23,
      "name": "Kafrul",
      "post_code": 1206,
      "division_name": "Dhaka",
      "zone_id": 1
    }
  ]
}
```

---

## 3. Get Areas By District Name (`GET /areas?district_name=...`)

Filters delivery hubs by administrative district (e.g., `Dhaka`, `Chattogram`, `Sylhet`, `Bogura`).

- **Method:** `GET`
- **Endpoint:** `/areas?district_name=<:district_name>`

### Query Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `district_name` | `string` | **Yes** | District name in English (case-insensitive substring match). |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/areas?district_name=Dhaka' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

---

## 📊 Area Object Schema

| Field Name | Type | Description |
| :--- | :---: | :--- |
| `id` | `number` | Unique numeric identifier. **Use this as `delivery_area_id` during parcel creation.** |
| `name` | `string` | Official hub name. **Use this as `delivery_area` during parcel creation.** |
| `post_code` | `number` | Official 4-digit Bangladesh postal code. |
| `division_name` | `string` | Administrative division (e.g. `Dhaka`, `Chittagong`, `Rajshahi`). |
| `zone_id` | `number` | Internal RedX logistical routing zone. |
