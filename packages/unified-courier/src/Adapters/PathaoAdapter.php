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

class PathaoAdapter implements CourierAdapterInterface
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $username;
    protected string $password;
    protected string $baseUrl;
    protected ?string $accessToken = null;

    public function __construct(array $config)
    {
        $this->clientId     = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->username     = $config['username'] ?? '';
        $this->password     = $config['password'] ?? '';
        $this->baseUrl      = rtrim($config['base_url'] ?? 'https://api-hermes.pathao.com', '/');
        $this->accessToken  = $config['access_token'] ?? null;
    }

    public function authenticate(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $payload = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username'      => $this->username,
            'password'      => $this->password,
            'grant_type'    => 'password',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/aladdin/api/v1/issue-token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($res, true);
        if (isset($json['access_token'])) {
            $this->accessToken = $json['access_token'];
            return $this->accessToken;
        }

        throw new RuntimeException('Failed to authenticate with Pathao API: ' . ($json['message'] ?? 'Unknown error'));
    }

    public function createOrder(OrderRequest $request): OrderResponse
    {
        $token = $this->authenticate();

        $payload = [
            'store_id'            => $request->storeId ?? ($request->extraParams['store_id'] ?? 1),
            'merchant_order_id'   => $request->invoiceId,
            'recipient_name'      => $request->recipientName,
            'recipient_phone'     => $request->recipientPhone,
            'recipient_address'   => $request->recipientAddress,
            'recipient_city'      => $request->extraParams['recipient_city'] ?? 1,
            'recipient_zone'      => $request->extraParams['recipient_zone'] ?? 1,
            'recipient_area'      => $request->extraParams['recipient_area'] ?? 1,
            'delivery_type'       => 48, // Standard 48h
            'item_type'           => 2,  // Parcel
            'special_instruction' => $request->specialNotes,
            'item_quantity'       => 1,
            'item_weight'         => (string)$request->weightKg,
            'amount_to_collect'   => (int)$request->amountToCollect,
            'item_description'    => $request->specialNotes ?? 'E-commerce goods',
        ];

        $response = $this->request('POST', '/aladdin/api/v1/orders', $payload, $token);

        if (isset($response['data']['consignment_id'])) {
            $data = $response['data'];
            $consignmentId = (string)$data['consignment_id'];
            $status = CourierStatus::normalize('pathao', $data['order_status'] ?? 'pending');
            return new OrderResponse(
                true,
                $consignmentId,
                $consignmentId,
                $status,
                (float)($data['delivery_fee'] ?? 65.0),
                'Pathao order booked successfully',
                $response
            );
        }

        return new OrderResponse(
            false,
            null,
            null,
            CourierStatus::UNKNOWN,
            0.0,
            $response['message'] ?? 'Failed to book Pathao order',
            $response
        );
    }

    public function trackOrder(string $trackingCode): TrackingResponse
    {
        $token = $this->authenticate();
        $response = $this->request('GET', "/aladdin/api/v1/orders/{$trackingCode}/info", [], $token);
        $data = $response['data'] ?? [];
        $status = CourierStatus::normalize('pathao', $data['order_status'] ?? 'unknown');

        return new TrackingResponse(
            $trackingCode,
            $status,
            null,
            $data['recipient_phone'] ?? null,
            [],
            date('c'),
            $response
        );
    }

    public function cancelOrder(string $trackingCode): bool
    {
        // Pathao cancellation via API
        return false;
    }

    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse
    {
        $base = ($zone === 'inside_dhaka') ? 65.0 : (($zone === 'dhaka_suburb') ? 100.0 : 135.0);
        $extraKg = max(0, ceil($weightKg - 1.0));
        $extraCharge = $extraKg * (($zone === 'inside_dhaka') ? 20.0 : 25.0);
        $codCharge = round($codAmount * 0.01);
        $total = $base + $extraCharge + $codCharge;

        return new RateResponse($base, $extraCharge, $codCharge, $total, ($zone === 'inside_dhaka') ? 'Same Day / 24h' : '48-72 Hours');
    }

    public function handleWebhook(array $payload, array $headers = []): WebhookEvent
    {
        $consignmentId = (string)($payload['consignment_id'] ?? '');
        $rawStatus     = (string)($payload['order_status'] ?? 'pending');
        $status        = CourierStatus::normalize('pathao', $rawStatus);

        return new WebhookEvent(
            'pathao',
            'order_status_update',
            $consignmentId,
            $consignmentId,
            $status,
            $payload
        );
    }

    protected function request(string $method, string $endpoint, array $data = [], string $token = ''): array
    {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'Authorization: Bearer ' . $token,
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
            return ['type' => 'error', 'message' => "cURL Error: {$err}"];
        }

        return json_decode($res, true) ?: ['type' => 'error', 'message' => 'Invalid JSON response from Pathao'];
    }
}
