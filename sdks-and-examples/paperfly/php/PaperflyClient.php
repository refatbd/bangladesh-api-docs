<?php
declare(strict_types=1);

namespace RefatBd\Paperfly;

use Exception;
use RuntimeException;

/**
 * Paperfly Courier API v2 PHP Client
 * 
 * Production-ready SDK for Paperfly Courier integration in Bangladesh.
 * 
 * @author Refat Rahman <https://refat.ovh>
 * @github https://github.com/refatbd
 * @license MIT
 */
class PaperflyClient
{
    public const BASE_URL             = 'https://api.paperfly.com.bd';
    public const DEFAULT_PAPERFLY_KEY = 'Paperfly_~La?Rj73FcLm';

    protected string $username;
    protected string $password;
    protected string $paperflyKey;
    protected string $baseUrl;
    protected int $timeout;

    /**
     * @param string $username    Merchant Panel User Name (e.g. sellerone)
     * @param string $password    Merchant Panel Password
     * @param string|null $paperflyKey Custom API key or uses default system key
     * @param string|null $baseUrl
     * @param int $timeout
     */
    public function __construct(
        string $username,
        string $password,
        ?string $paperflyKey = null,
        ?string $baseUrl = null,
        int $timeout = 30
    ) {
        $this->username    = trim($username);
        $this->password    = trim($password);
        $this->paperflyKey = $paperflyKey ?: self::DEFAULT_PAPERFLY_KEY;
        $this->baseUrl     = rtrim($baseUrl ?: self::BASE_URL, '/');
        $this->timeout     = $timeout;
    }

    /**
     * Create a standard delivery order
     * 
     * @param array $payload
     * @return array
     */
    public function createOrder(array $payload): array
    {
        return $this->request('POST', '/merchant/api/service/new_order_v2.php', $payload);
    }

    /**
     * Create an exchange delivery order
     * 
     * @param array $payload
     * @return array
     */
    public function createExchangeOrder(array $payload): array
    {
        $payload['orderType'] = 'Exchange';
        return $this->request('POST', '/merchant/api/service/new_order_v2.php', $payload);
    }

    /**
     * Track an order by merchant order reference or tracking number
     * 
     * @param string $referenceNumber
     * @return array
     */
    public function trackOrder(string $referenceNumber): array
    {
        return $this->request('POST', '/API-Order-Tracking', [
            'ReferenceNumber' => $referenceNumber
        ]);
    }

    /**
     * Cancel an active order before rider pickup
     * 
     * @param string $orderId
     * @return array
     */
    public function cancelOrder(string $orderId): array
    {
        return $this->request('POST', '/api/v1/cancel-order', [
            'order_id' => $orderId
        ]);
    }

    /**
     * Core HTTP Client Request Handler via cURL
     * 
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @return array
     * @throws RuntimeException
     */
    protected function request(string $method, string $endpoint, ?array $payload = null): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch  = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'paperflykey: ' . $this->paperflyKey,
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        // HTTP Basic Auth
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("Paperfly Network Failure: {$curlError}");
        }

        $decoded = json_decode((string)$responseBody, true);

        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? "HTTP {$httpCode}";
            throw new RuntimeException("Paperfly API Error [{$httpCode}]: {$msg}", $httpCode);
        }

        return is_array($decoded) ? $decoded : ['raw' => $responseBody];
    }
}
