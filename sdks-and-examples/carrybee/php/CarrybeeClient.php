<?php

declare(strict_types=1);

namespace Refatbd\Carrybee;

use Exception;
use RuntimeException;
use InvalidArgumentException;

/**
 * Class CarrybeeClient
 * Production-ready PHP SDK for Carrybee Courier API v2.0
 * 
 * @author Refat Rahman <https://refat.ovh | https://github.com/refatbd>
 * @license MIT
 */
class CarrybeeClient
{
    private string $clientId;
    private string $clientSecret;
    private string $clientContext;
    private string $baseUrl;
    private int $timeout;

    public const PRODUCTION_URL = 'https://developers.carrybee.com';
    public const SANDBOX_URL = 'https://sandbox.carrybee.com';

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $clientContext,
        bool $sandbox = false,
        int $timeout = 30
    ) {
        $this->clientId = trim($clientId);
        $this->clientSecret = trim($clientSecret);
        $this->clientContext = trim($clientContext);
        $this->baseUrl = $sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        $this->timeout = $timeout;

        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->clientContext)) {
            throw new InvalidArgumentException('Carrybee credentials (Client-ID, Client-Secret, Client-Context) cannot be empty.');
        }
    }

    // ==========================================
    // Location & Coverage Endpoints
    // ==========================================

    public function getCities(): array
    {
        return $this->request('GET', '/api/v2/cities');
    }

    public function getZones(int $cityId): array
    {
        return $this->request('GET', "/api/v2/zones/{$cityId}");
    }

    public function getAreaSuggestion(string $search): array
    {
        return $this->request('GET', '/api/v2/area-suggestion', [], ['search' => $search]);
    }

    public function resolveAddressDetails(string $address): array
    {
        return $this->request('POST', '/api/v2/address-details', ['address' => $address]);
    }

    // ==========================================
    // Store / Warehouse Endpoints
    // ==========================================

    public function getStores(): array
    {
        return $this->request('GET', '/api/v2/stores');
    }

    public function createStore(array $storeData): array
    {
        return $this->request('POST', '/api/v2/stores', $storeData);
    }

    // ==========================================
    // Order Management Endpoints
    // ==========================================

    public function createOrder(array $orderData): array
    {
        return $this->request('POST', '/api/v2/orders', $orderData);
    }

    public function createBulkOrders(array $orders): array
    {
        return $this->request('POST', '/api/v2/orders-bulk', ['orders' => $orders]);
    }

    public function trackOrder(string $consignmentId): array
    {
        return $this->request('GET', "/api/v2/orders/track/{$consignmentId}");
    }

    public function cancelOrder(string $consignmentId, string $reason = 'Merchant requested cancellation'): array
    {
        return $this->request('POST', '/api/v2/orders/cancel', [
            'consignment_id' => $consignmentId,
            'reason' => $reason
        ]);
    }

    // ==========================================
    // Reverse Pickup & Exchange Endpoints
    // ==========================================

    public function createReversePickup(array $pickupData): array
    {
        return $this->request('POST', '/api/v2/orders/reverse-pickup', $pickupData);
    }

    public function createExchangeOrder(string $consignmentId, array $exchangeData): array
    {
        return $this->request('POST', "/api/v2/orders/{$consignmentId}/exchange", $exchangeData);
    }

    // ==========================================
    // Webhook Handshake Verification Helper
    // ==========================================

    public static function verifyWebhookHandshake(?string $incomingHeader, string $configuredSecret): bool
    {
        if (empty($incomingHeader) || empty($configuredSecret)) {
            return false;
        }
        return hash_equals($configuredSecret, $incomingHeader);
    }

    // ==========================================
    // Core HTTP Engine (cURL)
    // ==========================================

    private function request(string $method, string $path, array $data = [], array $queryParams = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $headers = [
            'Client-ID: ' . $this->clientId,
            'Client-Secret: ' . $this->clientSecret,
            'Client-Context: ' . $this->clientContext,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        } elseif (strtoupper($method) === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("Carrybee API Connection Error: {$curlError}");
        }

        $decoded = json_decode((string)$response, true);
        if ($decoded === null && !empty($response)) {
            throw new RuntimeException("Invalid JSON response received from Carrybee API. HTTP Code: {$httpCode}. Raw: {$response}");
        }

        if ($httpCode >= 400) {
            $errorMessage = $decoded['message'] ?? "Carrybee API HTTP {$httpCode} Error";
            $errors = isset($decoded['errors']) ? ' Details: ' . json_encode($decoded['errors']) : '';
            throw new RuntimeException($errorMessage . $errors, $httpCode);
        }

        return $decoded ?? [];
    }
}
