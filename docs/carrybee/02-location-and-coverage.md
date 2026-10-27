# 02. Location & Geographical Coverage

Carrybee operates across a 3-tier geographical model: **City &rarr; Zone &rarr; Area**. Additionally, it provides AI-assisted address parsing and live autocomplete suggestion endpoints.

---

## 1. Get Cities (`GET /api/v2/cities`)

Retrieves all operational cities serviced by Carrybee across Bangladesh.

- **Method:** `GET`
- **Endpoint:** `/api/v2/cities`

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "City list fetched successfully",
  "data": {
    "cities": [
      { "id": 1, "name": "Bagerhat" },
      { "id": 14, "name": "Dhaka" },
      { "id": 15, "name": "Chittagong" }
    ]
  }
}
```

---

## 2. Get Zones in City (`GET /api/v2/cities/:city_id/zones`)

Fetches all delivery zones within a designated city.

- **Method:** `GET`
- **Endpoint:** `/api/v2/cities/<:city_id>/zones`

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "Zone list fetched successfully",
  "data": {
    "zones": [
      { "id": 10, "name": "Gulshan", "city_id": 14 },
      { "id": 151, "name": "Uttara sector 6", "city_id": 14 }
    ]
  }
}
```

---

## 3. Get Areas in Zone (`GET /api/v2/cities/:city_id/zones/:zone_id/areas`)

Fetches all sub-areas or neighborhood blocks within a specific zone.

- **Method:** `GET`
- **Endpoint:** `/api/v2/cities/<:city_id>/zones/<:zone_id>/areas`

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "Area list fetched successfully",
  "data": {
    "areas": [
      { "id": 100, "name": "Gulshan 1", "zone_id": 10 },
      { "id": 101, "name": "Gulshan 2", "zone_id": 10 }
    ]
  }
}
```

---

## 4. Area Autocomplete Suggestion (`GET /api/v2/area-suggestion?search=...`)

Searches for matching cities, zones, or areas by partial text match. Perfect for powering instant search dropdowns in e-commerce checkout forms.

- **Method:** `GET`
- **Endpoint:** `/api/v2/area-suggestion?search=<query>`
- **Constraint:** `search` query must be at least 3 characters.

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.carrybee.com/api/v2/area-suggestion?search=Uttara' \
  --header 'Client-ID: <client-id>' \
  --header 'Client-Secret: <client-secret>' \
  --header 'Client-Context: <client-context>'
```

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "Suggested area",
  "data": {
    "items": [
      {
        "city_id": 14,
        "city_name": "Dhaka",
        "zone_id": 151,
        "zone_name": "Uttara sector 6",
        "area_id": 5100,
        "area_name": "Alal Avenue"
      }
    ]
  }
}
```

---

## 5. Smart Address Parser (`POST /api/v2/address-details`)

Resolves a raw customer address string into appropriate `city_id` and `zone_id` integers automatically.

- **Method:** `POST`
- **Endpoint:** `/api/v2/address-details`
- **Constraint:** `query` string must be at least 10 characters.

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.carrybee.com/api/v2/address-details' \
  --header 'Client-ID: <client-id>' \
  --header 'Client-Secret: <client-secret>' \
  --header 'Client-Context: <client-context>' \
  --header 'Content-Type: application/json' \
  --data '{
    "query": "House 12, Road 4, Sector 10, Uttara, Dhaka"
  }'
```

### Sample Response (`200 OK`)

```json
{
  "error": false,
  "message": "Address details",
  "data": {
    "city_id": 14,
    "zone_id": 151
  }
}
```
