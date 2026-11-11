# 04. Consignment Tracking & Status API

Paperfly provides a dedicated endpoint to retrieve the full milestone progression of any consignment using your original merchant order reference.

---

## Track Order API

- **Method:** `POST`
- **Endpoint:** `https://api.paperfly.com.bd/API-Order-Tracking`

### Request Headers

```http
Authorization: Basic <base64(username:password)>
paperflykey: Paperfly_~La?Rj73FcLm
Content-Type: application/json
Accept: application/json
```

---

## Request Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `ReferenceNumber` | `string` | **Yes** | Your `merchantOrderReference` assigned when placing the order (or Paperfly `tracking_number`). |

### Sample Request (cURL)

```bash
curl --location 'https://api.paperfly.com.bd/API-Order-Tracking' \
  --header 'Content-Type: application/json' \
  --header 'paperflykey: Paperfly_~La?Rj73FcLm' \
  --header 'Authorization: Basic YOUR_BASE64_AUTH' \
  --data '{
    "ReferenceNumber": "INV_2026_9041"
  }'
```

---

## Sample Response (`200 OK`)

```json
{
  "success": {
    "message": "success",
    "trackingStatus": [
      {
        "invNum": "INV_2026_9041",
        "receivedAmount": "2500",
        "Pick": "Y",
        "PickTime": "2026-04-10 11:30:00",
        "inTransit": "Y",
        "inTransitTime": "2026-04-10 16:45:00",
        "ReceivedAtPoint": "Y",
        "ReceivedAtPointTime": "2026-04-11 08:20:00",
        "PickedForDelivery": "Y",
        "PickedForDeliveryTime": "2026-04-11 10:15:00",
        "Delivered": "Y",
        "DeliveredTime": "2026-04-11 14:05:30",
        "Returned": "",
        "ReturnedTime": "",
        "Partial": "",
        "PartialTime": "",
        "onHoldSchedule": "",
        "close": "Y",
        "closeTime": "2026-04-11 18:00:00"
      }
    ]
  },
  "response_code": 200
}
```

---

## 📊 Milestone Keys Explained

| Key Name | Timestamp Field | Description |
| :--- | :--- | :--- |
| `Pick` | `PickTime` | `"Y"` when rider physically collects package from merchant store. |
| `inTransit` | `inTransitTime` | `"Y"` when consignment is traversing central sorting hubs. |
| `ReceivedAtPoint` | `ReceivedAtPointTime` | `"Y"` when parcel arrives at destination delivery branch point. |
| `PickedForDelivery`| `PickedForDeliveryTime` | `"Y"` when rider departs branch for last-mile delivery. |
| `Delivered` | `DeliveredTime` | `"Y"` when recipient accepts package and pays COD amount. |
| `onHoldSchedule` | N/A | Contains date/time if recipient requested delivery postponement. |
| `Partial` | `PartialTime` | `"Y"` if customer accepted a partial portion of a multi-item shipment. |
| `Returned` | `ReturnedTime` | `"Y"` if package is returned back towards merchant origin. |
| `close` | `closeTime` | `"Y"` when financial reconciliation and ledger entry are completed. |
| `receivedAmount` | N/A | Actual cash collected from recipient in BDT. |
