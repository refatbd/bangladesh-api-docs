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

class SteadfastAdapter implements CourierAdapterInterface
{
    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(array $config)
    {
        $this->apiKey    = $config['api_key'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->baseUrl   = rtrim($config['base_url'] ?? 'https://portal.steadfast.com.bd/api/v1', '/');

        if (empty($this->apiKey) || empty($this->secretKey)) {
            throw new RuntimeException('SteadfastAdapter requires api_key and secret_key in config.');
        }
    }

    public function createOrder(OrderRequest $request): OrderResponse
    {
        $payload = [
            'invoice'           => $request->invoiceId,
            'recipient_name'    => $request->recipientName,
            'recipient_phone'   => $request->recipientPhone,
            'recipient_address' => $request->recipientAddress,
            'cod_amount'        => $request->amountToCollect,
            'note'              => $request->specialNotes,
        ];

        $response = $this->request('POST', '/create_order', $payload);

        if (($response['status'] ?? 0) === 200 && isset($response['consignment'])) {
            $data = $response['consignment'];
            $status = CourierStatus::normalize('steadfast', $data['status'] ?? 'pending');
            return new OrderResponse(
                true,
                (string)($data['consignment_id'] ?? ''),
                (string)($data['tracking_code'] ?? ''),
                $status,
                (float)($data['delivery_charge'] ?? 70.0),
                $response['message'] ?? 'Order booked successfully',
                $response
            );
        }

        return new OrderResponse(
            false,
            null,
            null,
            CourierStatus::UNKNOWN,
            0.0,
            $response['message'] ?? 'Failed to book Steadfast order',
            $response
        );
    }

    public function trackOrder(string $trackingCode): TrackingResponse
    {
        $response = $this->request('GET', "/status_by_trackingcode/{$trackingCode}");
        $status = CourierStatus::normalize('steadfast', $response['delivery_status'] ?? 'unknown');

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
        // Steadfast does not offer a direct cancel API; handled via support ticket or return
        return false;
    }

    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse
    {
        $base = ($zone === 'inside_dhaka') ? 70.0 : (($zone === 'dhaka_suburb') ? 100.0 : 135.0);
        $extraKg = max(0, ceil($weightKg - 1.0));
        $extraCharge = $extraKg * (($zone === 'inside_dhaka') ? 20.0 : 25.0);
        $codCharge = max(10.0, round($codAmount * 0.01));
        $total = $base + $extraCharge + $codCharge;

        return new RateResponse($base, $extraCharge, $codCharge, $total, ($zone === 'inside_dhaka') ? '24 Hours' : '48-72 Hours');
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookEvent
    {
        $trackingCode  = (string)($payload['tracking_code'] ?? '');
        $consignmentId = isset($payload['consignment_id']) ? (string)$payload['consignment_id'] : null;
        $rawStatus     = (string)($payload['status'] ?? 'pending');
        $status        = CourierStatus::normalize('steadfast', $rawStatus);

        return new WebhookEvent(
            'steadfast',
            'status_updated',
            $trackingCode,
            $consignmentId,
            $status,
            $payload
        );
    }

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'Api-Key: ' . $this->apiKey,
            'Secret-Key: ' . $this->secretKey,
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

        return json_decode($res, true) ?: ['status' => 500, 'message' => 'Invalid JSON response from Steadfast'];
    }
}
