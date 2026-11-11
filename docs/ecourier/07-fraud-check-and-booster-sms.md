# 07. AI Fraud Check & Booster VAS Services

eCourier integrates value-added digital intelligence directly into its courier infrastructure, enabling merchants to mitigate fraudulent return losses and automate customer communication.

---

## 1. Fraud Alert Detection (`POST /fraud-status-check`)

Before shipping high-value or customized goods via COD, merchants can query eCourier's aggregate logistics database to evaluate a customer's reliability.

### Request Body
```json
{
  "number": "01811223344"
}
```

### Potential Response Profiles

#### A. Trusted Customer (`Verified`)
```json
{
  "success": true,
  "response_code": 200,
  "customer_status": "Verified by eCourier",
  "message": ""
}
```

#### B. First-Time Buyer (`New Customer`)
```json
{
  "success": true,
  "response_code": 200,
  "customer_status": "New Customer for eCourier",
  "message": ""
}
```

#### C. High Risk / Suspicious Buyer (`Warning!`)
```json
{
  "success": true,
  "response_code": 200,
  "customer_status": "Warning!",
  "message": [
    "High parcel return frequency reported across merchant network"
  ]
}
```
*Action: Call customer to confirm commitment or request partial advance payment.*

---

## 2. Booster SMS Broadcasting (`POST /bs-sms`)

Broadcast automated order status updates, delivery OTPs, or marketing announcements directly using eCourier's telecommunications gateway. Costs are deducted directly from your pending COD balance.

### Request Body
```json
{
  "number": [
    "01811223344",
    "01700998877"
  ],
  "op_title": "booster",
  "text_type": "text",
  "op_body": "Your package ECR1030102170392 is out for delivery with eCourier. Please keep BDT 1850 ready.",
  "service_type": "mask"
}
```

| Parameter | Type | Details |
| :--- | :--- | :--- |
| `number` | `array` | List of 11-digit Bangladeshi mobile numbers |
| `op_title` | `string` | Service identifier (default `booster`) |
| `text_type` | `string` | `text` (standard SMS) |
| `op_body` | `string` | Message body (Supports English and Unicode Bengali) |
| `service_type` | `string` | `mask` (Sender ID masking) or `nonmask` |

### Response
```json
{
  "success": true,
  "response_code": 200,
  "message": "All SMS sent",
  "wrong": []
}
```

---

## 3. Booster Mobile Top-Up (`POST /bs-top`)

Automate mobile airtime balance recharge for store operators, delivery riders, or promotional campaign winners.

### Request Body
```json
{
  "number": "01811223344",
  "amount": 100,
  "operator_type": 1
}
```
*Note: `operator_type`: `1` = Prepaid, `2` = Postpaid. Amount must be between 50 and 1,000 BDT.*

### Response
```json
{
  "success": true,
  "response_code": 200,
  "transcation_id": "2388_1548834510_74832",
  "message": "Request Successfully Accepted. Your transaction ID is 2388_1548834510_74832"
}
```

---

## 4. OTP SMS Dispatch (`POST /bs-top-otp`)

High-priority transactional pipeline for two-factor authentication or delivery verification codes.

### Request Body
```json
{
  "number": "01811223344",
  "op_title": "booster",
  "text_type": "text",
  "op_body": "Your secure order confirmation code is 849201",
  "service_type": "mask"
}
```

### Response
```json
{
  "success": true,
  "response_code": 200,
  "message": "All SMS sent",
  "wrong": []
}
```
