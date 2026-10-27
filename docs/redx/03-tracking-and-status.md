# 03. Tracking & Lifecycle Status

RedX provides real-time shipment milestone tracking with localized status messages in both **English** and **Bengali** (`message_en`, `message_bn`), accompanied by ISO 8601 timestamps.

---

## Track Parcel (`GET /parcel/track/:tracking_id`)

Returns the chronological transit history of a consignment from placement to final fulfillment or return.

- **Method:** `GET`
- **Endpoint:** `/parcel/track/<:tracking_id>`
- **Sandbox URL:** `https://sandbox.redx.com.bd/v1.0.0-beta/parcel/track/:tracking_id`
- **Production URL:** `https://openapi.redx.com.bd/v1.0.0-beta/parcel/track/:tracking_id`

### Request Headers

```http
API-ACCESS-TOKEN: Bearer <jwt_token>
Accept: application/json
```

### URL Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `tracking_id` | `string` | **Yes** | The unique alphanumeric tracking identifier (e.g. `21A427TU4BN3R`). |

### Sample Request (cURL)

```bash
curl --location 'https://sandbox.redx.com.bd/v1.0.0-beta/parcel/track/21A427TU4BN3R' \
  --header 'API-ACCESS-TOKEN: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
```

---

## Response Structure

### Sample Response (`200 OK`)

```json
{
  "tracking": [
    {
      "message_en": "Package is created successfully",
      "message_bn": "পার্সেলটি সফল ভাবে প্লেস করা হয়েছে",
      "time": "2026-04-27T08:29:14.000Z"
    },
    {
      "message_en": "Package is picked up",
      "message_bn": "পার্সেল পিকাপ সম্পন্ন হয়েছে",
      "time": "2026-04-27T11:41:03.000Z"
    },
    {
      "message_en": "Received at sorting hub",
      "message_bn": "সর্টিং হাবে পার্সেল গৃহীত হয়েছে",
      "time": "2026-04-27T16:20:10.000Z"
    },
    {
      "message_en": "Out for delivery with rider",
      "message_bn": "রাইডারের মাধ্যমে ডেলিভারি চলমান রয়েছে",
      "time": "2026-04-28T09:15:00.000Z"
    },
    {
      "message_en": "Package delivered successfully",
      "message_bn": "পার্সেলটি সফলভাবে ডেলিভারি হয়েছে",
      "time": "2026-04-28T14:30:22.000Z"
    }
  ]
}
```

### Response Fields Breakdown

| Field | Type | Description |
| :--- | :---: | :--- |
| `tracking` | `array` | Chronological list of shipment event snapshots, starting from initial booking. |
| `tracking[].message_en` | `string` | Human-readable event description in English. |
| `tracking[].message_bn` | `string` | Official Bengali localization of the transit milestone. |
| `tracking[].time` | `string` | UTC datetime formatted according to ISO 8601 (`YYYY-MM-DDTHH:mm:ss.sssZ`). |

---

## 💻 Frontend Rendering Snippet (JavaScript)

```javascript
async function displayTrackingTimeline(trackingId, jwtToken) {
  const response = await fetch(`https://openapi.redx.com.bd/v1.0.0-beta/parcel/track/${trackingId}`, {
    headers: { 'API-ACCESS-TOKEN': `Bearer ${jwtToken}` }
  });
  const data = await response.json();

  const container = document.getElementById('trackingHistory');
  container.innerHTML = data.tracking.map(event => `
    <div class="timeline-item">
      <div class="time">${new Date(event.time).toLocaleString('en-US', { timeZone: 'Asia/Dhaka' })}</div>
      <div class="msg-en font-medium">${event.message_en}</div>
      <div class="msg-bn text-zinc-500">${event.message_bn}</div>
    </div>
  `).join('');
}
```
