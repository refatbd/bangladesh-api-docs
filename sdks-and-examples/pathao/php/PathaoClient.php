<?php
declare(strict_types=1);

namespace RefatBd\Pathao;

use Exception;
use RuntimeException;

/**
 * Pathao Courier Aladdin API v1 PHP Client
 * 
 * Production-ready SDK for Pathao Courier with automated OAuth 2.0 token management.
 * Supports Sandbox and Live Production environments.
 * 
 * @author Refat Rahman <https://refat.ovh>
 * @github https://github.com/refatbd
 * @license MIT
 */
class PathaoClient
{
    public const SANDBOX_URL    = 'https://courier-api-sandbox.pathao.com';
    public const PRODUCTION_URL = 'https://api-hermes.pathao.com';

    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $username;
    protected string $password;
    protected ?string $accessToken = null;
    protected ?string $refreshToken = null;
    protected int $timeout;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     * @param bool   $isSandbox
     * @param string|null $cachedAccessToken
     * @param string|null $cachedRefreshToken
     * @param int    $timeout
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $username,
        string $password,
        bool $isSandbox = false,
        ?string $cachedAccessToken = null,
        ?string $cachedRefreshToken = null,
        int $timeout = 30
    ) {
        $this->clientId     = trim($clientId);
        $this->clientSecret = trim($clientSecret);
        $this->username     = trim($username);
        $this->password     = trim($password);
        $this->baseUrl      = $isSandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        $this->accessToken  = $cachedAccessToken;
        $this->refreshToken = $cachedRefreshToken;
        $this->timeout      = $timeout;
    }

    /**
     * Authenticate and issue a new OAuth access token
     * 
     * @return array
     */
    public function issueToken(): array
    {
        $payload = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'password',
            'username'      => $this->username,
            'password'      => $this->password,
        ];

        $data = $this->rawRequest('POST', '/aladdin/api/v1/issue-token', $payload);
        if (!empty($data['access_token'])) {
            $this->accessToken  = $data['access_token'];
            $this->refreshToken = $data['refresh_token'] ?? null;
        }

        return $data;
    }

    /**
     * Renew an access token using refresh_token grant
     * 
     * @return array
     */
    public function refreshToken(): array
    {
        if (empty($this->refreshToken)) {
            return $this->issueToken();
        }

        $payload = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
        ];

        $data = $this->rawRequest('POST', '/aladdin/api/v1/issue-token', $payload);
        if (!empty($data['access_token'])) {
            $this->accessToken  = $data['access_token'];
            $this->refreshToken = $data['refresh_token'] ?? $this->refreshToken;
        }

        return $data;
    }

    /**
     * Get access token (fetches automatically if not present)
     * 
     * @return string
     */
    public function getAccessToken(): string
    {
        if (empty($this->accessToken)) {
            $this->issueToken();
        }
        return (string)$this->accessToken;
    }

    /**
     * Get refresh token for persistent storage
     * 
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Get list of cities
     * 
     * @return array
     */
    public function getCities(): array
    {
        return $this->authRequest('GET', '/aladdin/api/v1/city-list');
    }

    /**
     * Get zones inside a city
     * 
     * @param int $cityId
     * @return array
     */
    public function getZones(int $cityId): array
    {
        return $this->authRequest('GET', "/aladdin/api/v1/cities/{$cityId}/zone-list");
    }

    /**
     * Get areas inside a zone
     * 
     * @param int $zoneId
     * @return array
     */
    public function getAreas(int $zoneId): array
    {
        return $this->authRequest('GET', "/aladdin/api/v1/zones/{$zoneId}/area-list");
    }

    /**
     * Create a new merchant warehouse / store
     * 
     * @param array $payload
     * @return array
     */
    public function createStore(array $payload): array
    {
        return $this->authRequest('POST', '/aladdin/api/v1/stores', $payload);
    }

    /**
     * List all registered merchant stores
     * 
     * @return array
     */
    public function getStores(): array
    {
        return $this->authRequest('GET', '/aladdin/api/v1/stores');
    }

    /**
     * Create a single delivery order
     * 
     * @param array $payload
     * @return array
     */
    public function createOrder(array $payload): array
    {
        return $this->authRequest('POST', '/aladdin/api/v1/orders', $payload);
    }

    /**
     * Create bulk consignment orders
     * 
     * @param array $orders Array of order objects
     * @return array
     */
    public function createBulkOrders(array $orders): array
    {
        return $this->authRequest('POST', '/aladdin/api/v1/orders/bulk', ['orders' => $orders]);
    }

    /**
     * Get short status info for an order by consignment_id
     * 
     * @param string $consignmentId
     * @return array
     */
    public function getOrderInfo(string $consignmentId): array
    {
        return $this->authRequest('GET', '/aladdin/api/v1/orders/' . rawurlencode($consignmentId) . '/info');
    }

    /**
     * Calculate price plan for an order
     * 
     * @param int   $storeId
     * @param int   $itemType 1 for Document, 2 for Parcel
     * @param int   $deliveryType 48 for Normal, 12 for On-Demand
     * @param float $itemWeight in KG
     * @param int   $recipientCity
     * @param int   $recipientZone
     * @return array
     */
    public function calculatePrice(
        int $storeId,
        int $itemType,
        int $deliveryType,
        float $itemWeight,
        int $recipientCity,
        int $recipientZone
    ): array {
        return $this->authRequest('POST', '/aladdin/api/v1/merchant/price-plan', [
            'store_id'       => $storeId,
            'item_type'      => $itemType,
            'delivery_type'  => $deliveryType,
            'item_weight'    => $itemWeight,
            'recipient_city' => $recipientCity,
            'recipient_zone' => $recipientZone,
        ]);
    }

    /**
     * Authenticated request with auto token retry on 401
     * 
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @return array
     */
    protected function authRequest(string $method, string $endpoint, ?array $payload = null): array
    {
        $token = $this->getAccessToken();
        try {
            return $this->rawRequest($method, $endpoint, $payload, ['Authorization: Bearer ' . $token]);
        } catch (RuntimeException $e) {
            // Auto retry on 401 Unauthorized
            if ($e->getCode() === 401) {
                $this->refreshToken();
                $newToken = $this->getAccessToken();
                return $this->rawRequest($method, $endpoint, $payload, ['Authorization: Bearer ' . $newToken]);
            }
            throw $e;
        }
    }

    /**
     * Low-level cURL execution handler
     * 
     * @param string $method
     * @param string $endpoint
     * @param array|null $payload
     * @param array $headers
     * @return array
     * @throws RuntimeException
     */
    protected function rawRequest(string $method, string $endpoint, ?array $payload = null, array $headers = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $ch  = curl_init();

        $defaultHeaders = [
            'Content-Type: application/json; charset=UTF-8',
            'Accept: application/json',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("Pathao Network Failure: {$curlError}");
        }

        $decoded = json_decode((string)$responseBody, true);

        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? "HTTP {$httpCode}";
            throw new RuntimeException("Pathao API Error [{$httpCode}]: {$msg}", $httpCode);
        }

        return is_array($decoded) ? $decoded : ['raw' => $responseBody];
    }
}
