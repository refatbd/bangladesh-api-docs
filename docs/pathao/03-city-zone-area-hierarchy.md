# 03. City, Zone & Area Geographical Hierarchy

Pathao enforces a strict **3-tier cascading geographical directory** across Bangladesh:

```text
City (e.g., Dhaka, Chittagong, Rajshahi)
  └── Zone (e.g., Uttara, Mirpur, Dhanmondi)
        └── Area (e.g., Sector 10, Road 4, Bonolota)
```

Both store setup and parcel bookings rely on these identifiers.

---

## 1. Get List of Cities (`GET /aladdin/api/v1/city-list`)

Fetches all metropolitan cities and regional logistics divisions serviced by Pathao.

- **Method:** `GET`
- **Endpoint:** `/aladdin/api/v1/city-list`

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/city-list' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

### Sample Response (`200 OK`)

```json
{
  "message": "City successfully fetched.",
  "type": "success",
  "code": 200,
  "data": {
    "data": [
      {
        "city_id": 1,
        "city_name": "Dhaka"
      },
      {
        "city_id": 2,
        "city_name": "Chittagong"
      },
      {
        "city_id": 4,
        "city_name": "Rajshahi"
      }
    ]
  }
}
```

---

## 2. Get Zones in City (`GET /aladdin/api/v1/cities/:city_id/zone-list`)

Fetches all logistical zones inside a selected city.

- **Method:** `GET`
- **Endpoint:** `/aladdin/api/v1/cities/<:city_id>/zone-list`

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/cities/1/zone-list' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

### Sample Response (`200 OK`)

```json
{
  "message": "Zone list fetched.",
  "type": "success",
  "code": 200,
  "data": {
    "data": [
      {
        "zone_id": 1070,
        "zone_name": "Abdullahpur Uttara"
      },
      {
        "zone_id": 298,
        "zone_name": "60 feet"
      },
      {
        "zone_id": 1066,
        "zone_name": "Abul Hotel"
      }
    ]
  }
}
```

---

## 3. Get Areas in Zone (`GET /aladdin/api/v1/zones/:zone_id/area-list`)

Fetches micro-areas, roads, and sectors within a designated zone, indicating rider availability for home delivery and pickups.

- **Method:** `GET`
- **Endpoint:** `/aladdin/api/v1/zones/<:zone_id>/area-list`

### Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/zones/1070/area-list' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

### Sample Response (`200 OK`)

```json
{
  "message": "Area list fetched.",
  "type": "success",
  "code": 200,
  "data": {
    "data": [
      {
        "area_id": 37,
        "area_name": "Bonolota",
        "home_delivery_available": true,
        "pickup_available": true
      },
      {
        "area_id": 3,
        "area_name": "Road 03",
        "home_delivery_available": true,
        "pickup_available": true
      }
    ]
  }
}
```

### Field Flags

- `home_delivery_available` (`boolean`): If `false`, parcels cannot be dispatched to home addresses in this area (may require branch pickup).
- `pickup_available` (`boolean`): If `false`, merchant stores cannot be registered here for rider package collection.
