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

class RedXAdapter implements CourierAdapterInterface
{
    protected string $apiToken;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiToken = $config['api_token'] ?? '';
        $this->baseUrl  = rtrim($config['base_url'] ?? 'https://openapi.redx.com.bd/v1.0.0-beta', '/');

        if (empty($this->apiToken)) {
            throw new RuntimeException('RedXAdapter requires api_token in config.');
        }
    }

    public function createOrder(OrderRequest $request): OrderResponse
    {
        $payload = [
            'customer_name'           => $request->recipientName,
            'customer_phone'          => $request->recipientPhone,
            'delivery_area'           => $request->recipientAddress,
            'delivery_area_id'        => $request->extraParams['delivery_area_id'] ?? 1,
            'customer_address'        => $request->recipientAddress,
            'merchant_invoice_id'     => $request->invoiceId,
            'cash_collection_amount'  => (int)$request->amountToCollect,
            'parcel_weight'           => $request->getWeightInGrams(), // Grams
            'instruction'             => $request->specialNotes,
            'pickup_store_id'         => $request->storeId ?? ($request->extraParams['pickup_store_id'] ?? 1),
        ];

        $response = $this->request('POST', '/parcels', $payload);

        if (isset($response['tracking_id'])) {
            $trackingId = (string)$response['tracking_id'];
            return new OrderResponse(
                true,
                $trackingId,
                $trackingId,
                CourierStatus::PENDING,
                70.0,
                'RedX parcel created successfully',
                $response
            );
        }

        return new OrderResponse(
            false,
            null,
            null,
            CourierStatus::UNKNOWN,
            0.0,
            $response['message'] ?? 'Failed to create RedX parcel',
            $response
        );
    }

    public function trackOrder(string $trackingCode): TrackingResponse
    {
        $response = $this->request('GET', "/parcels/tracking/{$trackingCode}");
        $tracking = $response['tracking'] ?? [];
        $status = CourierStatus::normalize('redx', $tracking['status'] ?? 'unknown');

        $milestones = [];
        if (!empty($tracking['message'])) {
            $milestones[] = [
                'title'     => $tracking['status'] ?? 'UPDATE',
                'message'   => $tracking['message'],
                'timestamp' => date('c'),
            ];
        }

        return new TrackingResponse(
            $trackingCode,
            $status,
            null,
            null,
            $milestones,
            date('c'),
            $response
        );
    }

    public function cancelOrder(string $trackingCode): bool
    {
        // RedX does not have an open automated cancellation endpoint for merchants via v1.0.0-beta
        return false;
    }

    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse
    {
        $base = ($zone === 'inside_dhaka') ? (($weightKg <= 0.5) ? 60.0 : 70.0) : (($zone === 'dhaka_suburb') ? 100.0 : 130.0);
        $extraKg = max(0, ceil($weightKg - 1.0));
        $extraCharge = $extraKg * (($zone === 'inside_dhaka') ? 20.0 : 25.0);
        $codCharge = round($codAmount * 0.01);
        $total = $base + $extraCharge + $codCharge;

        return new RateResponse($base, $extraCharge, $codCharge, $total, ($zone === 'inside_dhaka') ? '24-48 Hours' : '48-72 Hours');
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookEvent
    {
        $trackingCode  = (string)($payload['tracking_id'] ?? '');
        $rawStatus     = (string)($payload['status'] ?? 'pending');
        $status        = CourierStatus::normalize('redx', $rawStatus);

        return new WebhookEvent(
            'redx',
            'parcel_status_updated',
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
            'API-ACCESS-TOKEN: Bearer ' . $this->apiToken,
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

        return json_decode($res, true) ?: ['status' => 500, 'message' => 'Invalid JSON from RedX'];
    }
}
