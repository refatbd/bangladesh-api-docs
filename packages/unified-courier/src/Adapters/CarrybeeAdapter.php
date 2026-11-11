<?php

namespace Refatbd\Courier\Adapters;

use Refatbd\Courier\Contracts\CourierAdapterInterface;
use Refatbd\Courier\DTO\OrderRequest;
use Refatbd\Courier\DTO\OrderResponse;
use Refatbd\Courier\DTO\TrackingResponse;
use Refatbd\Courier\DTO\RateResponse;
use Refatbd\Courier\DTO\WebhookEvent;
use Refatbd\Courier\Enums\CourierStatus;
use RuntimeException;

class CarrybeeAdapter implements CourierAdapterInterface
{
    protected string $apiToken;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiToken = $config['api_token'] ?? '';
        $this->baseUrl  = rtrim($config['base_url'] ?? 'https://merchant.carrybee.com/api/v2.0', '/');
    }

    public function createOrder(OrderRequest $request): OrderResponse
    {
        $payload = [
            'store_id'          => $request->storeId ?? ($request->extraParams['store_id'] ?? 1),
            'order_id'          => $request->invoiceId,
            'customer_name'     => $request->recipientName,
            'customer_phone'    => $request->recipientPhone,
            'delivery_address'  => $request->recipientAddress,
            'city_id'           => $request->extraParams['city_id'] ?? 1,
            'zone_id'           => $request->extraParams['zone_id'] ?? 1,
            'area_id'           => $request->extraParams['area_id'] ?? 1,
            'collectable_amount'=> (float)$request->amountToCollect,
            'actual_weight'     => (float)$request->weightKg,
            'item_description'  => $request->specialNotes ?? 'Standard parcel',
        ];

        $response = $this->request('POST', '/orders/create', $payload);

        if (isset($response['data']['consignment_id'])) {
            $consignmentId = (string)$response['data']['consignment_id'];
            $trackingId = (string)($response['data']['tracking_id'] ?? $consignmentId);
            return new OrderResponse(
                true,
                $consignmentId,
                $trackingId,
                CourierStatus::PENDING,
                65.0,
                'Carrybee order created successfully',
                $response
            );
        }

        return new OrderResponse(
            false,
            null,
            null,
            CourierStatus::UNKNOWN,
            0.0,
            $response['message'] ?? 'Failed to create Carrybee order',
            $response
        );
    }

    public function trackOrder(string $trackingCode): TrackingResponse
    {
        $response = $this->request('GET', "/orders/track/{$trackingCode}");
        $data = $response['data'] ?? [];
        $status = CourierStatus::normalize('carrybee', $data['order_status'] ?? 'unknown');

        return new TrackingResponse(
            $trackingCode,
            $status,
            null,
            null,
            [],
            date('c'),
            $response
        );
    }

    public function cancelOrder(string $trackingCode): bool
    {
        $response = $this->request('POST', "/orders/cancel/{$trackingCode}");
        return isset($response['success']) && $response['success'] === true;
    }

    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse
    {
        $base = ($zone === 'inside_dhaka') ? 65.0 : (($zone === 'dhaka_suburb') ? 100.0 : 130.0);
        $extraKg = max(0, ceil($weightKg - 1.0));
        $extraCharge = $extraKg * (($zone === 'inside_dhaka') ? 20.0 : 25.0);
        $codCharge = round($codAmount * 0.01);
        $total = $base + $extraCharge + $codCharge;

        return new RateResponse($base, $extraCharge, $codCharge, $total, ($zone === 'inside_dhaka') ? '24 Hours' : '48-72 Hours');
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookEvent
    {
        $trackingCode = (string)($payload['consignment_id'] ?? ($payload['order_id'] ?? ''));
        $rawStatus    = (string)($payload['status'] ?? 'order_placed');
        $status       = CourierStatus::normalize('carrybee', $rawStatus);

        return new WebhookEvent(
            'carrybee',
            'order_status_update',
            $trackingCode,
            $trackingCode,
            $status,
            $payload
        );
    }

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'Authorization: Bearer ' . $this->apiToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['status' => 500, 'message' => "cURL Error: {$err}"];
        }

        return json_decode($res, true) ?: ['status' => 500, 'message' => 'Invalid JSON response from Carrybee'];
    }
}
