# 03. Exchange Order Processing

Paperfly offers a native **Exchange Order** workflow designed specifically for e-commerce merchants dealing with size swaps, defective item replacements, or product exchanges. 

During an exchange delivery, the Paperfly rider hands over the replacement product to the customer and simultaneously collects the return parcel in a single doorstep trip.

---

## Exchange Order Endpoint

Exchange orders are submitted to the same order creation endpoint, but with four mandatory exchange directive fields:

- **Method:** `POST`
- **Endpoint:** `https://api.paperfly.com.bd/merchant/api/service/new_order_v2.php`

---

## Exchange-Specific Parameters

In addition to all standard order creation fields, include the following:

| Parameter | Type | Required | Description |
| :--- | :---: | :---: | :--- |
| `orderType` | `string` | **Yes** | Must be set to literal string `"Exchange"`. |
| `exchangeDescription` | `string` | **Yes** | Detailed description of the item the rider must collect back from the customer (e.g. `Return Size M, Blue T-Shirt`). |
| `exchangePrice` | `string` | **Yes** | Additional COD amount to collect from the customer for the exchange, or `"0"` if it's an even swap. |
| `exchangeWeight` | `string` | **Yes** | Estimated weight of the returning exchange item in KG (e.g. `"0.5"`). |

---

## Sample Request (cURL)

```bash
curl --location 'https://api.paperfly.com.bd/merchant/api/service/new_order_v2.php' \
  --header 'Content-Type: application/json' \
  --header 'paperflykey: Paperfly_~La?Rj73FcLm' \
  --header 'Authorization: Basic YOUR_BASE64_AUTH' \
  --data '{
    "merchantOrderReference": "EXC_2026_1012",
    "storeName": "Apex Outlet",
    "productBrief": "Men Leather Shoes - Size 43 (Replacement)",
    "packagePrice": "0",
    "max_weight": "1.0",
    "customerName": "Liton Ovi",
    "customerAddress": "House 12, Road 4, Banani, Dhaka",
    "customerPhone": "01610202717",
    "orderType": "Exchange",
    "exchangeDescription": "Damaged Size 42 Shoes (Return to warehouse)",
    "exchangePrice": "100",
    "exchangeWeight": "1.0"
  }'
```

---

## Sample Response (`200 OK`)

```json
{
  "success": {
    "message": "successfully inserted",
    "tracking_number": "Z-051125-63821-A3-A2",
    "tracking_barcode": "751820115460"
  },
  "response_code": 200
}
```

> [!TIP]
> Instruct your customer care team to advise the customer to have the return product packed in polybag before the rider arrives to expedite the exchange handover.
