# Multi-Courier Shipping Rate & COD Calculator

Welcome to the interactive delivery rate matrix for Bangladeshi logistics providers. This tool compares real-time delivery costs, cash-on-delivery (COD) processing fees, and service level agreements (SLA) across **Steadfast, RedX, Pathao, Paperfly, Carrybee, and eCourier**.

---

<div id="rateCalculatorApp" class="my-8 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-[#121316] shadow-sm">
  <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 border-b border-zinc-200 dark:border-zinc-800 gap-4">
    <div>
      <h3 class="text-base font-bold text-zinc-900 dark:text-white flex items-center gap-2">
        <span>⚡ Interactive Courier Rate Engine</span>
        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Live 2026 Rates</span>
      </h3>
      <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Adjust parcel metrics to compare net shipping charges across all 6 logistics services.</p>
    </div>
    <div class="flex items-center gap-2 text-xs">
      <span class="text-zinc-400">Base Currency:</span>
      <span class="font-mono font-bold text-zinc-800 dark:text-zinc-200 px-2 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800">BDT (৳)</span>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-5 pb-6">
<div>
  <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
    Parcel Weight: <span id="weightVal" class="text-blue-600 dark:text-sky-400 font-bold">1.0 kg</span>
  </label>
  <input type="range" id="inputWeight" min="0.5" max="10.0" step="0.5" value="1.0"
    class="w-full h-2 bg-zinc-200 dark:bg-zinc-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
  <div class="flex justify-between text-[10px] text-zinc-400 mt-1 font-mono">
    <span>0.5 kg</span>
    <span>2.5 kg</span>
    <span>5.0 kg</span>
    <span>10.0 kg</span>
  </div>
</div>

<div>
  <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Delivery Destination Zone</label>
  <select id="selectZone" class="w-full text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
    <option value="inside_dhaka" selected>Inside Dhaka (Metro / City Corporation)</option>
    <option value="dhaka_suburb">Dhaka Suburbs (Savar, Gazipur, Keraniganj, Narayanganj)</option>
    <option value="outside_dhaka">Outside Dhaka (All 63 Districts / Thanas)</option>
  </select>
</div>

<div>
  <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Cash on Delivery (COD Collection)</label>
  <div class="relative">
    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs text-zinc-400 font-bold">৳</span>
    <input type="number" id="inputCod" value="1500" min="0" step="100"
      class="w-full text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 pl-6 p-2 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono">
  </div>
  <p class="text-[10px] text-zinc-400 mt-1">Standard 1% COD fee automatically computed.</p>
</div>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left text-xs border-collapse">
      <thead>
        <tr class="border-b border-zinc-200 dark:border-zinc-800 text-[11px] font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
          <th class="py-2.5 px-3">Courier Provider</th>
          <th class="py-2.5 px-3">Base Shipping</th>
          <th class="py-2.5 px-3">Weight Extra</th>
          <th class="py-2.5 px-3">1% COD Fee</th>
          <th class="py-2.5 px-3 font-bold text-zinc-900 dark:text-white">Net Merchant Charge</th>
          <th class="py-2.5 px-3">Est. Delivery SLA</th>
          <th class="py-2.5 px-3 text-right">API Reference</th>
        </tr>
      </thead>
      <tbody id="rateTableBody" class="divide-y divide-zinc-100 dark:divide-zinc-800/60 font-mono">
      </tbody>
    </table>
  </div>
</div>

---

## Standard Courier Tariff Architecture in Bangladesh

Most logistics partners in Bangladesh share a common billing formula with nuanced edge-cases regarding weight brackets:

$$\text{Total Charge} = \text{Base Delivery Fee} + \max(0, \lceil \text{Weight} - \text{Base Threshold} \rceil) \times \text{Extra Kg Charge} + (\text{COD Amount} \times 1\%)$$

### Provider Comparison Matrix

| Provider | Inside Dhaka (Up to 1kg) | Dhaka Suburb | Outside Dhaka (Up to 1kg) | Extra Per Kg (Inside / Outside) | COD Fee | Return Charge |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Steadfast** | ৳70 | ৳100 | ৳130 - ৳140 | +৳20 / +৳25 | 1% (Min ৳10) | 50% or Free |
| **RedX** | ৳60 (0.5kg) / ৳70 (1kg) | ৳100 | ৳130 | +৳20 / +৳25 | 1% | 50% |
| **Pathao** | ৳60 - ৳70 | ৳100 | ৳130 - ৳140 | +৳20 / +৳25 | 1% | 50% |
| **Paperfly** | ৳60 (Doorstep) | ৳90 | ৳120 - ৳130 | +৳20 / +৳25 | 1% | 50% |
| **Carrybee** | ৳60 - ৳70 | ৳100 | ৳130 | +৳20 / +৳25 | 1% | 0% - 50% |
| **eCourier** | ৳60 (W-1 500g) / ৳70 (1kg)| ৳100 | ৳130 - ৳150 | +৳20 / +৳30 | 1% | 50% |

> [!NOTE]
> All rates above exclude 15% VAT mandated by the National Board of Revenue (NBR) unless explicitly absorbed in your merchant agreement contract.

---

## Programmatic Pricing API Endpoints

If you prefer to query price calculation dynamically via API before order placement:

### 1. Pathao Courier Dynamic Pricing
```bash
curl -X POST https://api-hermes.pathao.com/aladdin/api/v1/merchant/price-plan \
  -H "Authorization: Bearer <ACCESS_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "store_id": 1234,
    "item_type": 1,
    "delivery_type": 48,
    "item_weight": "1.5",
    "recipient_city": 1,
    "recipient_zone": 2
  }'
```

### 2. RedX Delivery Dynamic Pricing
```bash
curl -X POST https://openapi.redx.com.bd/v1.0.0-beta/pricing \
  -H "API-ACCESS-TOKEN: Bearer <API_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{
    "delivery_area_id": 105,
    "pickup_store_id": 12,
    "weight": 1000
  }'
```

### 3. eCourier Package Estimation
```bash
curl -X POST https://backoffice.ecourier.com.bd/api/packages \
  -H "API-KEY: <API_KEY>" \
  -H "API-SECRET: <API_SECRET>" \
  -H "USER-ID: <USER_ID>"
```
