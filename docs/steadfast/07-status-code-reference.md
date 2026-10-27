# 07. Status Codes & Error Reference

> **Steadfast Courier API Documentation**  
> Maintained by **[Refat Rahman](https://github.com/refatbd)** ([refat.ovh](https://refat.ovh)).

---

## 🚚 1. Delivery Status State Machine

> [!IMPORTANT]
> **Approval Pending Rule**: Any status ending in `_approval_pending` represents the rider's immediate field report, which has not yet been audited by Steadfast's central accounts team. **Do not close your internal financial books on approval pending states.** Wait until the status transitions to `delivered`, `partial_delivered`, or `cancelled`.

| Delivery Status | Description | Financial Implication |
| :--- | :--- | :--- |
| `in_review` | Just created; awaiting Steadfast central hub approval. Every parcel starts here. | Booking pending |
| `pending` | Approved by Steadfast hub; awaiting rider assignment and collection attempt. | In transit pipeline |
| `hold` | On hold due to incorrect address, wrong phone, or customer request. | Action required |
| `delivered_approval_pending` | The delivery rider marked parcel delivered; awaiting accounts confirmation. | Provisional delivery |
| `partial_delivered_approval_pending` | The rider marked parcel partially delivered; awaiting confirmation. | Provisional partial |
| `cancelled_approval_pending` | The rider marked parcel cancelled / rejected; awaiting confirmation. | Provisional return |
| `unknown_approval_pending` | Delivery outcome pending audit by regional hub manager. | In review |
| **`delivered`** | **Delivered & Audited.** Customer accepted order and paid COD. | **COD owed to merchant** |
| **`partial_delivered`** | **Partially Delivered & Audited.** Customer accepted part of the parcel. | **Partial COD owed** |
| **`cancelled`** | **Delivery Cancelled & Audited.** Parcel returns back to merchant. | Return to merchant (RTO) |
| `exceptional` | Lost, damaged, or diverted off normal route. Contact support. | Claim investigation |
| `unknown` | State not recognized. Contact support. | Under review |

---

## ↩️ 2. Return Statuses (RTO)

Returned only by `/status_with_return_status_by_cid/{consignment_id}` when a parcel did not reach full delivery:

| Return Status Code | Stage in Return Journey |
| :--- | :--- |
| `cancelled_return_proccessing` | Cancelled; parcel is being prepared at destination hub for return trip. |
| `cancelled_return_rider_assigned` | Cancelled; return rider has been dispatched to return parcel to merchant. |
| `cancelled_return_received` | **Completed.** Merchant has physically received the returned parcel. |
| `partial_delivered_return_proccessing` | Part delivered; remaining items prepared for return. |
| `partial_delivered_return_rider_assigned` | Part delivered; rider assigned to return remaining items. |
| `partial_delivered_return_received` | Part delivered; merchant has received remaining items back. |

---

## 🛑 3. HTTP Response Status Codes

| HTTP Code | Meaning | What to Do |
| :---: | :--- | :--- |
| `200` | **Success / OK** | Request succeeded. Always inspect the JSON body (`status: 200`). |
| `400` | **Bad Request** | Malformed JSON or missing required fields in bulk orders. Check request format. |
| `401` | **Unauthorized** | Missing, invalid, or revoked `Api-Key` or `Secret-Key`. **Do not retry in a loop!** |
| `403` | **Forbidden** | Merchant profile is inactive or KYC documents are pending approval. |
| `422` | **Unprocessable Entity** | Field validation failure (e.g. invalid phone number). Read `errors` object. |
| `429` | **Too Many Requests** | Rate limit exceeded (>1,000 req/min) or locked out for 60 min after 10 failed 401s. Back off. |
| `500` | **Internal Server Error** | Steadfast server failure. Safe to retry with the same invoice (idempotent). |
