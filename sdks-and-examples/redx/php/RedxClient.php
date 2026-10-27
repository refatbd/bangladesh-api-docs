<?php
declare(strict_types=1);

namespace RefatBd\Redx;

use Exception;
use RuntimeException;

/**
 * RedX Delivery API v1.0.0-beta PHP Client
 * 
 * Production-ready SDK for RedX Courier API integration.
 * Supports Sandbox & Live Production environments.
 * 
 * @author Refat Rahman <https://refat.ovh>
 * @github https://github.com/refatbd
 * @license MIT
 */
class RedxClient
{
    public const SANDBOX_URL    = 'https://sandbox.redx.com.bd/v1.0.0-beta';
    public const PRODUCTION_URL = 'https://openapi.redx.com.bd/v1.0.0-beta';

    protected string $baseUrl;
    protected string $jwtToken;
    protected int $timeout;

    /**
     * @param string $jwtToken RedX Merchant JWT Bearer Token
     * @param bool   $isSandbox Set to true for sandbox environment, false for live production
     * @param int    $timeout   HTTP request timeout in seconds
     */
    public function __construct(string $jwtToken, bool $isSandbox = false, int $timeout = 30)
    {
        $this->jwtToken = trim($jwtToken);
        $this->baseUrl  = $isSandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        $this->timeout  = $timeout;
    }

    /**
     * Track a parcel's transit milestone history
     * 
     * @param string $trackingId
     * @return array
     */
    public function trackParcel(string $trackingId): array
    {
        return $this->request('GET', '/parcel/track/' . rawurlencode($trackingId));
    }

    /**
     * Get detailed information for a parcel
     * 
     * @param string $trackingId
     * @return array
     */
    public function getParcelInfo(string $trackingId): array
    {
        return $this->request('GET', '/parcel/info/' . rawurlencode($trackingId));
    }

    /**
     * Create a new parcel booking
     * 
     * @param array $payload Parcel parameters
     * @return array Contains tracking_id
     */
    public function createParcel(array $payload): array
    {
        return $this->request('POST', '/parcel', $payload);
    }

    /**
     * Update a parcel property (e.g. status, address)
     * 
     * @param string $trackingId
     * @param string $propertyName
     * @param string $newValue
     * @param string $reason
     * @return array
     */
    public function updateParcel(string $trackingId, string $propertyName, string $newValue, string $reason = ''): array
    {
        $body = [
            'entity_type'    => 'parcel-tracking-id',
            'entity_id'      => $trackingId,
            'update_details' => [
                'property_name' => $propertyName,
                'new_value'     => $newValue,
                'reason'        => $reason,
            ]
        ];

        return $this->request('PATCH', '/parcels', $body);
    }

    /**
     * Cancel an active parcel booking
     * 
     * @param string $trackingId
     * @param string $reason
     * @return array
     */
    public function cancelParcel(string $trackingId, string $reason = 'Merchant cancelled'): array
    {
        return $this->updateParcel($trackingId, 'status', 'cancelled', $reason);
    }

    /**
     * Get all delivery areas
     * 
     * @return array
     */
    public function getAreas(): array
    {
        return $this->request('GET', '/areas');
    }

    /**
     * Get delivery areas filtered by postal code
     * 
     * @param int|string $postCode
     * @return array
     */
    public function getAreasByPostCode($postCode): array
    {
        return $this->request('GET', '/areas?post_code=' . rawurlencode((string)$postCode));
    }

    /**
     * Get delivery areas filtered by district name
     * 
     * @param string $districtName
     * @return array
     */
    public function getAreasByDistrict(string $districtName): array
    {
        return $this->request('GET', '/areas?district_name=' . rawurlencode($districtName));
    }

    /**
     * Create a new pickup store / warehouse
     * 
     * @param string $name
     * @param string $phone
     * @param string $address
     * @param int    $areaId
     * @return array
     */
    public function createPickupStore(string $name, string $phone, string $address, int $areaId): array
    {
        return $this->request('POST', '/pickup/store', [
            'name'    => $name,
            'phone'   => $phone,
            'address' => $address,
            'area_id' => $areaId,
        ]);
    }

    /**
     * List all registered pickup stores
     * 
     * @return array
     */
    public function getPickupStores(): array
    {
        return $this->request('GET', '/pickup/stores');
    }

    /**
     * Get single pickup store details
     * 
     * @param int $storeId
     * @return array
     */
    public function getPickupStoreInfo(int $storeId): array
    {
        return $this->request('GET', '/pickup/store/info/' . $storeId);
    }

    /**
     * Calculate delivery charge & Cash-on-Delivery cost
     * 
     * @param int   $deliveryAreaId
     * @param int   $pickupAreaId
     * @param float $cashCollection
     * @param float $weightInGrams
     * @return array
     */
    public function calculateCharge(int $deliveryAreaId, int $pickupAreaId, float $cashCollection, float $weightInGrams): array
    {
        $query = http_build_query([
            'delivery_area_id'       => $deliveryAreaId,
            'pickup_area_id'         => $pickupAreaId,
            'cash_collection_amount' => $cashCollection,
            'weight'                 => $weightInGrams,
        ]);

        return $this->request('GET', '/charge/charge_calculator?' . $query);
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
            'API-ACCESS-TOKEN: Bearer ' . $this->jwtToken,
            'Accept: application/json',
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                $jsonData = json_encode($payload, JSON_THROW_ON_ERROR);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                $headers[] = 'Content-Type: application/json';
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($payload !== null) {
                $jsonData = json_encode($payload, JSON_THROW_ON_ERROR);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                $headers[] = 'Content-Type: application/json';
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("RedX API cURL Network Failure: {$curlError}");
        }

        $decoded = json_decode((string)$responseBody, true);

        if ($httpCode >= 400) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? "RedX API returned HTTP {$httpCode}";
            throw new RuntimeException("RedX API Error [{$httpCode}]: {$msg}", $httpCode);
        }

        return is_array($decoded) ? $decoded : ['raw' => $responseBody];
    }
}
