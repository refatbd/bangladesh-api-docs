# 06. Milestone Tracking, Label Print & Finance

This chapter documents operational lifecycle management: real-time parcel tracking, printing barcode shipping labels, cancelling pending pickups, and reconciling COD payments.

---

## 1. Parcel Tracking (`POST /track`)

eCourier provides detailed audit logging of each movement in a parcel's delivery lifecycle. You can query status using either the eCourier tracking ID (`ecr`) or your merchant internal invoice number (`product_id`).

### Request Formats
```json
{
  "ecr": "ECR1030102170392"
}
```
*Or by merchant product/invoice ID:*
```json
{
  "product_id": "INV-2026-901"
}
```

### Response Schema (Data Found)

```json
{
  "response_code": 200,
  "message": "Success",
  "query_data": [
    {
      "REFID": "ECR1030102170392",
      "company": "Refat Store",
      "product_id": "INV-2026-901",
      "r_name": "Tanvir Ahmed",
      "r_mobile": "01811223344",
      "r_address": "House 14, Road 3, Block D, Middle Badda, Dhaka",
      "r_area": "Badda",
      "r_type": "BOX",
      "r_time": "2026-10-27 12:00:09",
      "agent": "Kamrul Hassan",
      "paymentmethod": "COD",
      "paymentclear": "Clear",
      "r_timing": "Next Day(24hr)",
      "r_coverage": "Inside Dhaka",
      "r_weight": "Up To 1kg",
      "product_price": "1850",
      "shipping_charge": "60",
      "cod": "1850",
      "status": [
        ["Delivered", "Delivered to recipient", "2026-10-28 14:30:00"],
        ["On the way to Delivery", "Status has been changed", "2026-10-28 10:15:00"],
        ["In Destination Branch", "Arrived at Badda Branch", "2026-10-28 07:45:00"],
        ["In Transit", "Dispatched to Destination Hub", "2026-10-27 22:30:00"],
        ["In Source Branch", "Scanned at Origin Hub", "2026-10-27 18:00:00"],
        ["Picked Up", "Rider collected parcel", "2026-10-27 15:20:00"],
        ["Initiated", "Order submitted via API", "2026-10-27 12:00:09"]
      ]
    }
  ]
}
```

---

## 2. Dynamic Label Printing (`POST /label-print`)

Returns a direct URL to a generated thermal shipping invoice / barcode label PDF.

### Request Body
```json
{
  "tracking": "ECR1030102170392"
}
```

### Response
```json
{
  "success": true,
  "response_code": 200,
  "query_data": "https://staging.ecourier.com.bd/invoices/print_slip1604833594_2160.pdf"
}
```
*Your application can fetch this PDF stream to display inline or send directly to thermal sticker printers.*

---

## 3. Order Cancellation (`POST /cancel-order`)

Orders can be programmatically cancelled **only while their status is `Initiated` or `Initiated API`** (before a field rider scans and picks up the parcel).

### Request Body
```json
{
  "tracking": "ECR1030102170392",
  "comment": "Customer called to cancel shipment"
}
```

### Responses
- **Successfully Cancelled**:
  ```json
  {
    "success": "true",
    "message": "Order canceled"
  }
  ```
- **Already In-Transit / Locked**:
  ```json
  {
    "success": "false",
    "message": "Order not canceled"
  }
  ```

---

## 4. Payment & COD Settlement Status (`POST /payment-status`)

Provides financial reconciliation for completed deliveries, detailing the exact amount collected and the deducted fees.

### Request Body
```json
{
  "tracking": "ECR1030102170392"
}
```

### Response
```json
{
  "success": true,
  "message": {
    "payment_id": 20841,
    "payment_mode": "dbbl",
    "collected_amount": 1850,
    "payment_date": "2026-10-29 16:30:00",
    "shipping_charge": 60,
    "booking_price": 8,
    "fragile_price": 0,
    "cash_on_delivery": 1782
  }
}
```

### Financial Fields Breakdown
- `collected_amount`: Gross cash collected by rider from customer.
- `shipping_charge`: Base delivery fee.
- `booking_price`: Operational booking charge.
- `fragile_price`: Surcharge for special handling if `is_fragile: 1`.
- `cash_on_delivery`: Net disbursement payable to merchant account ($\text{Collected} - \text{Total Charges}$).
- `payment_mode`: Bank or MFS channel (`dbbl`, `scb`, `bkash`, `brac`).
