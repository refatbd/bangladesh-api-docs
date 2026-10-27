# 02. Standard Order Creation

Merchants place single parcel consignments into the Paperfly network via the `new_order_v2.php` service. Upon successful registration, Paperfly generates both an alphanumeric `tracking_number` and a numeric `tracking_barcode`.

---

## Create New Order

- **Method:** `POST`
- **Endpoint:** `https://api.paperfly.com.bd/merchant/api/service/new_order_v2.php`

### Request Headers

```http
Authorization: Basic <base64(username:password)>
paperflykey: Paperfly_~La?Rj73FcLm
Content-Type: application/json
Accept: application/json
```

---

## Request Parameters

| Parameter | Type | Required | Constraints / Description |
| :--- | :---: | :---: | :--- |
| `merchantOrderReference` | `string` | **Yes** | Your internal unique invoice or order reference (e.g. `INV-9041`). |
| `storeName` | `string` | **Yes** | Your registered merchant shop or pickup brand name. |
| `productBrief` | `string` | **Yes** | Short summary of consignment items (e.g. `Winter Hoodie`). |
| `packagePrice` | `string` | **Yes** | COD amount in BDT to collect from recipient upon delivery (`"0"` if prepaid). |
| `max_weight` | `string` | **Yes** | Total shipment weight in **Kilograms** (e.g. `"0.5"`, `"1.2"`). |
| `customerName` | `string` | **Yes** | Full name of the recipient customer. |
| `customerAddress` | `string` | **Yes** | Street, road, building, and area address (e.g. `Banani, Dhaka`). |
| `customerPhone` | `string` | **Yes** | Recipient's 11-digit mobile phone number (e.g. `01711223344`). |

---

## Sample Request (cURL)

```bash
curl --location 'https://api.paperfly.com.bd/merchant/api/service/new_order_v2.php' \
  --header 'Content-Type: application/json' \
  --header 'paperflykey: Paperfly_~La?Rj73FcLm' \
  --header 'Authorization: Basic YOUR_BASE64_AUTH' \
  --data '{
    "merchantOrderReference": "INV_2026_9041",
    "storeName": "Apex Outlet",
    "productBrief": "Men Leather Shoes",
    "packagePrice": "2500",
    "max_weight": "1.0",
    "customerName": "Liton Ovi",
    "customerAddress": "House 12, Road 4, Banani, Dhaka",
    "customerPhone": "01610202717"
  }'
```

---

## Sample Response (`200 OK`)

```json
{
  "success": {
    "message": "successfully inserted",
    "tracking_number": "Z-051125-63821-A3-A1",
    "tracking_barcode": "751820115459"
  },
  "response_code": 200
}
```

### Response Field Descriptions

| Field | Type | Description |
| :--- | :---: | :--- |
| `tracking_number` | `string` | Paperfly's unique shipment tracking code (e.g. `Z-051125-63821-A3-A1`). |
| `tracking_barcode` | `string` | 12-digit numeric Code-128 barcode number suitable for thermal parcel label printing. |
| `message` | `string` | Status message confirming database insertion. |
| `response_code` | `integer` | HTTP status code (200 on success). |
