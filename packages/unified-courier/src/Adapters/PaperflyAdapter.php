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

class PaperflyAdapter implements CourierAdapterInterface
{
    protected string $username;
    protected string $password;
    protected string $key;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->key      = $config['key'] ?? '';
        $this->baseUrl  = rtrim($config['base_url'] ?? 'https://api.paperfly.com.bd/v2', '/');
    }

    public function createOrder(OrderRequest $request): OrderResponse
    {
        $payload = [
            'merOrderRef'    => $request->invoiceId,
            'custName'       => $request->recipientName,
            'custPhone'      => $request->recipientPhone,
            'custAddr'       => $request->recipientAddress,
            'packagePrice'   => (int)$request->amountToCollect,
            'packageWeight'  => (string)$request->weightKg,
            'brief'          => $request->specialNotes ?? 'Standard parcel',
        ];

        $response = $this->request('POST', '/order-placement', $payload);

        if (isset($response['response_code']) && $response['response_code'] == 200) {
            $trackingNumber = (string)($response['success']['tracking_number'] ?? $request->invoiceId);
            return new OrderResponse(
                true,
                $trackingNumber,
                $trackingNumber,
                CourierStatus::PENDING,
                60.0,
                'Paperfly order placed successfully',
                $response
            );
        }

        return new OrderResponse(
            false,
            null,
            null,
            CourierStatus::UNKNOWN,
            0.0,
            $response['error'] ?? 'Failed to place Paperfly order',
            $response
        );
    }

    public function trackOrder(string $trackingCode): TrackingResponse
    {
        $response = $this->request('POST', '/tracking', ['ReferenceNumber' => $trackingCode]);
        $status = CourierStatus::normalize('paperfly', $response['PackageStatus'] ?? 'unknown');

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
        $response = $this->request('POST', '/order-cancel', ['ReferenceNumber' => $trackingCode]);
        return isset($response['response_code']) && $response['response_code'] == 200;
    }

    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse
    {
        $base = ($zone === 'inside_dhaka') ? 60.0 : (($zone === 'dhaka_suburb') ? 90.0 : 125.0);
        $extraKg = max(0, ceil($weightKg - 1.0));
        $extraCharge = $extraKg * (($zone === 'inside_dhaka') ? 20.0 : 25.0);
        $codCharge = round($codAmount * 0.01);
        $total = $base + $extraCharge + $codCharge;

        return new RateResponse($base, $extraCharge, $codCharge, $total, ($zone === 'inside_dhaka') ? '24 Hours' : '48-72 Hours');
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookEvent
    {
        $trackingCode = (string)($payload['ReferenceNumber'] ?? '');
        $rawStatus    = (string)($payload['Status'] ?? 'order_placed');
        $status       = CourierStatus::normalize('paperfly', $rawStatus);

        return new WebhookEvent(
            'paperfly',
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
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
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
            return ['response_code' => 500, 'error' => "cURL Error: {$err}"];
        }

        return json_decode($res, true) ?: ['response_code' => 500, 'error' => 'Invalid JSON from Paperfly'];
    }
}
