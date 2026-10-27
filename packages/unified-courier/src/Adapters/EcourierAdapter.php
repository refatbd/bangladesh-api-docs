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

class EcourierAdapter implements CourierAdapterInterface
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $userId;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey    = $config['api_key'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? '';
        $this->userId    = $config['user_id'] ?? '';
        $this->baseUrl   = rtrim($config['base_url'] ?? 'https://backoffice.ecourier.com.bd/api', '/');
    }

    public function createOrder(OrderRequest $request): OrderResponse
    {
        $payload = [
            'recipient_name'     => $request->recipientName,
            'recipient_mobile'   => $request->recipientPhone,
            'recipient_city'     => $request->extraParams['recipient_city'] ?? 'Dhaka',
            'recipient_area'     => $request->extraParams['recipient_area'] ?? 'Dhanmondi',
            'recipient_address'  => $request->recipientAddress,
            'package_code'       => $request->extraParams['package_code'] ?? '#7329',
            'product_price'      => (int)$request->amountToCollect,
            'payment_method'     => ($request->amountToCollect > 0) ? 'COD' : 'PREPAID',
            'recipient_landmark' => $request->specialNotes ?? '',
            'parcel_detail'      => $request->invoiceId,
        ];

        $response = $this->request('POST', '/order-place', $payload);

        if (isset($response['response_code']) && $response['response_code'] == 200) {
            $trackingId = (string)($response['ID'] ?? $response['tracking_id'] ?? '');
            return new OrderResponse(
                true,
                $trackingId,
                $trackingId,
                CourierStatus::PENDING,
                70.0,
                'eCourier parcel booked successfully',
                $response
            );
        }

        return new OrderResponse(
            false,
            null,
            null,
            CourierStatus::UNKNOWN,
            0.0,
            $response['message'] ?? 'Failed to place eCourier order',
            $response
        );
    }

    public function trackOrder(string $trackingCode): TrackingResponse
    {
        $response = $this->request('POST', '/track', ['ecr' => $trackingCode]);
        $status = CourierStatus::normalize('ecourier', $response['status'] ?? 'unknown');

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
        $response = $this->request('POST', '/order-cancel', ['tracking_id' => $trackingCode]);
        return isset($response['response_code']) && $response['response_code'] == 200;
    }

    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse
    {
        $base = ($zone === 'inside_dhaka') ? (($weightKg <= 0.5) ? 60.0 : 70.0) : (($zone === 'dhaka_suburb') ? 100.0 : 135.0);
        $extraKg = max(0, ceil($weightKg - 1.0));
        $extraCharge = $extraKg * (($zone === 'inside_dhaka') ? 20.0 : 30.0);
        $codCharge = round($codAmount * 0.01);
        $total = $base + $extraCharge + $codCharge;

        return new RateResponse($base, $extraCharge, $codCharge, $total, ($zone === 'inside_dhaka') ? '24-48 Hours' : '48-72 Hours');
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookEvent
    {
        $trackingCode = (string)($payload['tracking_id'] ?? ($payload['ID'] ?? ''));
        $rawStatus    = (string)($payload['status'] ?? 'pending');
        $status       = CourierStatus::normalize('ecourier', $rawStatus);

        return new WebhookEvent(
            'ecourier',
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
            'API-KEY: ' . $this->apiKey,
            'API-SECRET: ' . $this->apiSecret,
            'USER-ID: ' . $this->userId,
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
            return ['response_code' => 500, 'message' => "cURL Error: {$err}"];
        }

        return json_decode($res, true) ?: ['response_code' => 500, 'message' => 'Invalid JSON from eCourier'];
    }
}
