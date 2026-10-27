# Interactive Phone Fraud Risk Inspector

E-commerce merchants in Bangladesh suffer an estimated **8% to 22% COD return rate** due to unverified buyers, imposter numbers, and serial returners. Every returned parcel costs the merchant ৳100–৳200 in unrecoverable round-trip logistics and repackaging expenses.

This interactive tool allows you to inspect customer fraud risk scores before shipping orders, using **Steadfast Courier's Real-Time Score Engine** and **eCourier's AI Fraud Check Service**.

---

<div id="fraudCheckerApp" class="my-8 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-[#121316] shadow-sm">
  <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-zinc-200 dark:border-zinc-800 gap-4">
    <div>
      <h3 class="text-base font-bold text-zinc-900 dark:text-white flex items-center gap-2">
        <span>🛡️ Phone Number Fraud Inspector</span>
        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400">Live Simulator</span>
      </h3>
      <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Validate customer parcel success ratio and historical return metrics across the national delivery network.</p>
    </div>
    <div class="flex items-center gap-2">
      <label class="text-xs text-zinc-500 dark:text-zinc-400 cursor-pointer flex items-center gap-1.5">
        <input type="checkbox" id="fraudDemoMode" checked class="rounded border-zinc-300 dark:border-zinc-700 text-blue-600 focus:ring-0">
        <span>Use Sandbox / Demo Data</span>
      </label>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-5 pb-5">
<div>
  <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Customer Mobile Number (11 Digits)</label>
  <div class="relative">
    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-zinc-400 font-mono">+88</span>
    <input type="text" id="fraudPhoneInput" value="01700000000" maxlength="11" placeholder="017XXXXXXXX"
      class="w-full text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 pl-11 p-2 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono">
  </div>
  <p class="text-[10px] text-zinc-400 mt-1">E.g., 01700000000, 01812345678, 01999999999</p>
</div>

<div>
  <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Fraud Detection Provider</label>
  <select id="fraudProviderSelect" class="w-full text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
    <option value="steadfast" selected>Steadfast Courier (Free API Score)</option>
    <option value="ecourier">eCourier (AI Fraud Alert VAS)</option>
  </select>
  <p class="text-[10px] text-zinc-400 mt-1">Steadfast offers open ratio metrics; eCourier is a paid VAS.</p>
</div>

<div class="flex items-end">
  <button id="btnCheckFraud" class="w-full py-2 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs flex items-center justify-center gap-2 shadow-sm transition">
    <span>Inspect Fraud Score</span>
    <span>→</span>
  </button>
</div>
  </div>

  <div id="fraudResultContainer" class="mt-4 pt-5 border-t border-zinc-200 dark:border-zinc-800">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      
<div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 flex flex-col justify-between">
  <div class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Risk Level</div>
  <div id="resRiskBadge" class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1.5">
    <span>🟢 Safe Buyer</span>
  </div>
  <div id="resRecommendation" class="text-[11px] text-emerald-700 dark:text-emerald-300 mt-2">
    Recommended: Full COD Approved without extra advance deposit.
  </div>
</div>

<div class="p-4 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
  <div class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Delivery Success Ratio</div>
  <div id="resSuccessRate" class="text-2xl font-extrabold text-zinc-900 dark:text-white mt-1 font-mono">96.4%</div>
  <div class="w-full bg-zinc-200 dark:bg-zinc-800 h-1.5 rounded-full mt-2 overflow-hidden">
    <div id="resProgressBar" class="bg-emerald-500 h-full rounded-full" style="width: 96.4%;"></div>
  </div>
</div>

<div class="p-4 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
  <div class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Historical Orders</div>
  <div id="resTotalOrders" class="text-2xl font-extrabold text-zinc-900 dark:text-white mt-1 font-mono">28 Parcels</div>
  <div id="resOrderBreakdown" class="text-[11px] text-zinc-400 mt-2 font-mono">27 Delivered • 1 Cancelled</div>
</div>

<div class="p-4 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
  <div class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Data Source</div>
  <div id="resProviderLabel" class="text-sm font-bold text-zinc-800 dark:text-zinc-200 mt-1">Steadfast Network</div>
  <div class="text-[10px] text-zinc-400 mt-2">Aggregated across all verified e-commerce merchant dispatches nationwide.</div>
</div>

</div>
  </div>
</div>

---

## Technical Integration Guide

### 1. Steadfast Fraud Score API Reference

Steadfast provides the simplest and most accessible fraud check endpoint:

* **Endpoint:** `GET https://portal.steadfast.com.bd/api/v1/fraud_check/score/{phone}`
* **Authentication:** `Api-Key` and `Secret-Key` in HTTP request headers.

```php
<?php
// PHP Integration in Laravel / E-commerce Controller
$phone = '01700000000';

$response = Http::withHeaders([
    'Api-Key'    => config('services.steadfast.api_key'),
    'Secret-Key' => config('services.steadfast.secret_key'),
])->get("https://portal.steadfast.com.bd/api/v1/fraud_check/score/{$phone}");

$data = $response->json();

if ($response->successful() && isset($data['data'])) {
    $total     = $data['data']['total_parcels'] ?? 0;
    $delivered = $data['data']['delivered_parcels'] ?? 0;
    $returned  = $data['data']['cancelled_parcels'] ?? 0;
    
    $successRatio = $total > 0 ? round(($delivered / $total) * 100, 1) : 100;

    if ($total >= 3 && $successRatio < 70) {
        // High Risk Customer: Demand ৳150 delivery advance via Nagad/bKash
        return response()->json([
            'status' => 'high_risk',
            'action' => 'require_advance_payment',
            'success_rate' => $successRatio
        ]);
    }
}
```

### 2. eCourier AI Fraud Check

* **Endpoint:** `POST https://backoffice.ecourier.com.bd/api/fraud-status-check`
* **Headers:** `API-KEY`, `API-SECRET`, `USER-ID`

```javascript
// Node.js Express Middleware
const axios = require('axios');

async function checkFraudRisk(req, res, next) {
  const customerMobile = req.body.customer_phone;

  try {
    const { data } = await axios.post(
      'https://backoffice.ecourier.com.bd/api/fraud-status-check',
      { mobile: customerMobile },
      {
        headers: {
          'API-KEY': process.env.ECOURIER_API_KEY,
          'API-SECRET': process.env.ECOURIER_API_SECRET,
          'USER-ID': process.env.ECOURIER_USER_ID,
        }
      }
    );

    if (data.fraud_status === 'RISKY') {
      req.fraudAlert = true;
    }
    next();
  } catch (error) {
    // Fail-open strategy to avoid blocking genuine orders on network timeouts
    next();
  }
}
```
