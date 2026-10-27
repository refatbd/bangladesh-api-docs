<?php

declare(strict_types=1);

namespace Steadfast;

/**
 * Standalone Production-Ready Steadfast Courier API Client
 *
 * Authored & Maintained by Refat Rahman (https://github.com/refatbd)
 * Website: https://refat.ovh
 *
 * @license MIT
 */
class SteadfastClient
{
    public const DEFAULT_BASE_URL = 'https://portal.packzy.com/api/v1';

    protected string $apiKey;
    protected string $secretKey;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct(
        string $apiKey,
        string $secretKey,
        ?string $baseUrl = null,
        int $timeout = 20
    ) {
        $this->apiKey = trim($apiKey);
        $this->secretKey = trim($secretKey);
        $this->baseUrl = rtrim($baseUrl ?: self::DEFAULT_BASE_URL, '/');
        $this->timeout = $timeout;
    }

    /**
     * Diagnostic ping check (requires no API keys)
     */
    public function ping(): array
    {
        return $this->request('GET', '/ping', [], false);
    }

    /**
     * Retrieve current merchant COD wallet balance
     */
    public function getBalance(): array
    {
        return $this->request('GET', '/get_balance');
    }

    /**
     * Book a single parcel with automatic character sanitization and length bounds
     */
    public function createOrder(array $params): array
    {
        $sanitize = function (?string $text, int $max): string {
            if ($text === null) return '';
            $cleaned = str_replace(['{', '}', ';', '<', '>', '$'], ' ', $text);
            return mb_substr(trim($cleaned), 0, $max);
        };

        $payload = [
            'invoice'           => $sanitize($params['invoice'] ?? ('INV-' . time()), 100),
            'recipient_name'    => $sanitize($params['recipient_name'] ?? 'Customer', 100),
            'recipient_phone'   => $sanitize($params['recipient_phone'] ?? '', 40),
            'recipient_address' => $sanitize($params['recipient_address'] ?? '', 490),
            'cod_amount'        => max(0, (float) ($params['cod_amount'] ?? 0.0)),
            'note'              => $sanitize($params['note'] ?? '', 480),
        ];

        return $this->request('POST', '/create_order', $payload);
    }

    /**
     * Bulk create up to 500 orders
     */
    public function createBulkOrder(array $orders, bool $extended = false): array
    {
        $endpoint = $extended ? '/create_order/bulk-order/extended' : '/create_order/bulk-order';
        return $this->request('POST', $endpoint, ['data' => $orders]);
    }

    /**
     * Query delivery status by Consignment ID
     */
    public function getStatusByCid(int|string $consignmentId): array
    {
        return $this->request('GET', "/status_by_cid/{$consignmentId}");
    }

    /**
     * Query status with granular return progress stage
     */
    public function getStatusWithReturn(int|string $consignmentId): array
    {
        return $this->request('GET', "/status_with_return_status_by_cid/{$consignmentId}");
    }

    /**
     * Query status by internal merchant invoice number
     */
    public function getStatusByInvoice(string $invoice): array
    {
        $encoded = urlencode($invoice);
        return $this->request('GET', "/status_by_invoice/{$encoded}");
    }

    /**
     * Query status by customer tracking code
     */
    public function getStatusByTrackingCode(string $trackingCode): array
    {
        $encoded = urlencode($trackingCode);
        return $this->request('GET', "/status_by_trackingcode/{$encoded}");
    }

    /**
     * Retrieve chronological tracking history timeline
     */
    public function getTrackingTimeline(string $invoice): array
    {
        $encoded = urlencode($invoice);
        return $this->request('GET', "/trackings_by_invoice/{$encoded}");
    }

    /**
     * Request rider pickup from registered address
     */
    public function createPickupRequest(array $params): array
    {
        return $this->request('POST', '/create_pickup_request', $params);
    }

    /**
     * Request pre-delivery parcel recall/return
     */
    public function createReturnRequest(int|string $consignmentId, string $reason): array
    {
        return $this->request('POST', '/create_return_request', [
            'consignment_id' => $consignmentId,
            'reason'         => $reason,
        ]);
    }

    /**
     * Paginated list of return requests (10 per page)
     */
    public function getReturnRequests(int $page = 1): array
    {
        return $this->request('GET', "/get_return_requests?page={$page}");
    }

    /**
     * Paginated list of payment payouts (10 per page)
     */
    public function getPayments(int $page = 1): array
    {
        return $this->request('GET', "/payments?page={$page}");
    }

    /**
     * Detailed settlement breakdown for a payout
     */
    public function getPaymentDetails(int|string $paymentId): array
    {
        return $this->request('GET', "/payments/{$paymentId}");
    }

    /**
     * List of delivery police stations / thanas with districts
     */
    public function getPoliceStations(): array
    {
        return $this->request('GET', '/police_stations');
    }

    /**
     * Customer fraud check & delivery completion score
     */
    public function checkFraudScore(string $phone): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        return $this->request('GET', "/fraud_check/score/{$cleanPhone}");
    }

    /**
     * Underlying HTTP execution via cURL
     */
    protected function request(string $method, string $path, array $data = [], bool $withAuth = true): array
    {
        $url = $this->baseUrl . $path;
        $ch = curl_init();

        $headers = [
            'Accept: application/json',
        ];

        if ($withAuth) {
            $headers[] = 'Api-Key: ' . $this->apiKey;
            $headers[] = 'Secret-Key: ' . $this->secretKey;
        }

        if (strtoupper($method) === 'POST') {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $raw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($raw === false) {
            return [
                'success' => false,
                'status'  => 0,
                'message' => 'Network error: ' . $curlError,
            ];
        }

        $decoded = json_decode($raw, true) ?? [];
        $isSuccess = $httpCode >= 200 && $httpCode < 300;

        return [
            'success'   => $isSuccess,
            'http_code' => $httpCode,
            'data'      => $decoded,
        ];
    }
}
