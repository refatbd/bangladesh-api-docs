# 06. Tracking & Order Status

Pathao allows merchants and automated order management bots to query the real-time status, financial invoice link, and lifecycle state of any active or fulfilled shipment using its unique `consignment_id`.

---

## Get Order Short Info (`GET /aladdin/api/v1/orders/:consignment_id/info`)

- **Method:** `GET`
- **Endpoint:** `/aladdin/api/v1/orders/<:consignment_id>/info`

### Request Headers

```http
Authorization: Bearer <access_token>
Accept: application/json
```

### URL Parameters

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `consignment_id` | `string` | **Yes** | The alphanumeric unique consignment identifier returned during order creation. |

---

## Sample Request (cURL)

```bash
curl --location 'https://courier-api-sandbox.pathao.com/aladdin/api/v1/orders/PT9041A4B89/info' \
  --header 'Authorization: Bearer YOUR_ACCESS_TOKEN'
```

---

## Sample Response (`200 OK`)

```json
{
  "message": "Order info",
  "type": "success",
  "code": 200,
  "data": {
    "consignment_id": "PT9041A4B89",
    "merchant_order_id": "INV-2026-9041",
    "order_status": "Pending",
    "order_status_slug": "Pending",
    "updated_at": "2026-11-20 15:11:40",
    "invoice_id": null
  }
}
```

### Response Schema Breakdown

| Property | Type | Description |
| :--- | :---: | :--- |
| `consignment_id` | `string` | The Pathao consignment identifier. |
| `merchant_order_id` | `string` | Your internal order invoice ID provided during creation. |
| `order_status` | `string` | Human-readable title of current status (e.g. `Pending`, `Delivered`). |
| `order_status_slug` | `string` | Programmatic status key for backend switch statements. |
| `updated_at` | `string` | Timestamp when the status last transitioned (`YYYY-MM-DD HH:mm:ss`). |
| `invoice_id` | `string`/`null` | Numeric remittance disbursement invoice ID when COD cash has been settled to the merchant. |

---

## 💻 Sample Status Sync Script (Node.js)

```javascript
async function syncOrderStatus(consignmentId, accessToken) {
  const url = `https://api-hermes.pathao.com/aladdin/api/v1/orders/${consignmentId}/info`;
  const response = await fetch(url, {
    headers: { 'Authorization': `Bearer ${accessToken}` }
  });
  const json = await response.json();

  if (json.code === 200) {
    const { order_status_slug, invoice_id, updated_at } = json.data;
    console.log(`Order ${consignmentId} is currently ${order_status_slug} (Invoice: ${invoice_id})`);
    return order_status_slug;
  }
  throw new Error(json.message);
}
```
