<?php

declare(strict_types=1);

namespace Refatbd\Ecourier;

use Exception;
use RuntimeException;
use InvalidArgumentException;

/**
 * Class EcourierClient
 * Production-grade PHP SDK for eCourier Courier API v5.5
 * 
 * @author Refat Rahman <https://refat.ovh | https://github.com/refatbd>
 * @license MIT
 */
class EcourierClient
{
    private string $apiKey;
    private string $apiSecret;
    private string $userId;
    private string $baseUrl;
    private int $timeout;

    public const PRODUCTION_URL = 'https://backoffice.ecourier.com.bd/api';
    public const STAGING_URL = 'https://staging.ecourier.com.bd/api';

    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $userId,
        bool $sandbox = false,
        int $timeout = 30
    ) {
        $this->apiKey = trim($apiKey);
        $this->apiSecret = trim($apiSecret);
        $this->userId = trim($userId);
        $this->baseUrl = $sandbox ? self::STAGING_URL : self::PRODUCTION_URL;
        $this->timeout = $timeout;

        if (empty($this->apiKey) || empty($this->apiSecret) || empty($this->userId)) {
            throw new InvalidArgumentException('eCourier credentials (API-KEY, API-SECRET, USER-ID) cannot be empty.');
        }
    }

    // ==========================================
    // 1. Geographic & Coverage Endpoints
    // ==========================================

    public function getCities(): array
    {
        return $this->request('/city-list');
    }

    public function getThanas(string $city): array
    {
        return $this->request('/thana-list', ['city' => $city]);
    }

    public function getAreasByPostcode(string $postcode): array
    {
        return $this->request('/area-list', ['postcode' => $postcode]);
    }

    public function getPostcodes(string $city, string $thana): array
    {
        return $this->request('/postcode-list', [
            'city' => $city,
            'thana' => $thana
        ]);
    }

    public function getAreasByDistrict(string $district): array
    {
        return $this->request('/area-by-district', ['district' => $district]);
    }

    public function getBranches(): array
    {
        return $this->request('/branch-list');
    }

    public function getHubInfoByArea(string $area): array
    {
        return $this->request('/hub-info-by-area', ['area' => $area]);
    }

    // ==========================================
    // 2. Package Management Endpoints
    // ==========================================

    public function getPackages(): array
    {
        return $this->request('/packages');
    }

    // ==========================================
    // 3. Order Placement & Reseller Endpoints
    // ==========================================

    public function placeOrder(array $orderData): array
    {
        return $this->request('/order-place', $orderData);
    }

    public function placeResellerOrder(array $resellerOrderData): array
    {
        return $this->request('/order-place-reseller', $resellerOrderData);
    }

    public function cancelOrder(string $tracking, string $comment = 'Customer requested cancellation'): array
    {
        return $this->request('/cancel-order', [
            'tracking' => $tracking,
            'comment' => $comment
        ]);
    }

    public function cancelChildOrder(string $tracking, string $comment = 'Child partner cancelled'): array
    {
        return $this->request('/cancel-order-child', [
            'tracking' => $tracking,
            'comment' => $comment
        ]);
    }

    // ==========================================
    // 4. Milestone Tracking & Invoice Labels
    // ==========================================

    public function track(array $params): array
    {
        return $this->request('/track', $params);
    }

    public function trackByEcr(string $ecr): array
    {
        return $this->track(['ecr' => $ecr]);
    }

    public function trackByProductId(string $productId): array
    {
        return $this->track(['product_id' => $productId]);
    }

    public function trackChild(string $ecr): array
    {
        return $this->request('/track-child', ['ecr' => $ecr]);
    }

    public function getLabelPrintUrl(string $tracking): array
    {
        return $this->request('/label-print', ['tracking' => $tracking]);
    }

    public function getPaymentStatus(string $tracking): array
    {
        return $this->request('/payment-status', ['tracking' => $tracking]);
    }

    // ==========================================
    // 5. Fraud Alert & Booster VAS Services
    // ==========================================

    public function checkFraudStatus(string $phoneNumber): array
    {
        return $this->request('/fraud-status-check', ['number' => $phoneNumber]);
    }

    public function sendBoosterSms(array $smsData): array
    {
        return $this->request('/bs-sms', $smsData);
    }

    public function sendBoosterTopUp(string $number, int $amount, int $operatorType = 1): array
    {
        return $this->request('/bs-top', [
            'number' => $number,
            'amount' => $amount,
            'operator_type' => $operatorType
        ]);
    }

    public function getBoosterTopUpStatus(string $transactionId): array
    {
        return $this->request('/bs-top-status', ['transaction_id' => $transactionId]);
    }

    public function sendBoosterOtp(string $number, string $otpBody, string $serviceType = 'mask'): array
    {
        return $this->request('/bs-top-otp', [
            'number' => $number,
            'op_title' => 'booster',
            'text_type' => 'text',
            'op_body' => $otpBody,
            'service_type' => $serviceType
        ]);
    }

    // ==========================================
    // Core HTTP Engine (cURL with POST enforcement)
    // ==========================================

    private function request(string $path, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        $headers = [
            'API-KEY: ' . $this->apiKey,
            'API-SECRET: ' . $this->apiSecret,
            'USER-ID: ' . $this->userId,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("eCourier API Connection Failure: {$curlError}");
        }

        $decoded = json_decode((string)$response, true);
        if ($decoded === null && !empty($response)) {
            throw new RuntimeException("Invalid JSON response from eCourier (HTTP {$httpCode}): {$response}");
        }

        if ($httpCode >= 400) {
            $errorMessage = "eCourier API HTTP {$httpCode} Error";
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                $errorMessage .= ' - ' . implode('; ', array_filter($decoded['errors']));
            } elseif (isset($decoded['message'])) {
                $errorMessage .= ' - ' . (is_array($decoded['message']) ? json_encode($decoded['message']) : $decoded['message']);
            }
            throw new RuntimeException($errorMessage, $httpCode);
        }

        return $decoded ?? [];
    }
}
