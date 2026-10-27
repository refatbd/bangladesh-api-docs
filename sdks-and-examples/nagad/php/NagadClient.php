<?php

declare(strict_types=1);

namespace Refatbd\Nagad;

use Exception;
use RuntimeException;
use InvalidArgumentException;

/**
 * Class NagadClient
 * Production-ready PHP SDK for Nagad Direct Online Payment Gateway (API v3.3)
 * 
 * @author Refat Rahman <https://refat.ovh | https://github.com/refatbd>
 * @license MIT
 */
class NagadClient
{
    private string $merchantId;
    private string $merchantPrivateKey;
    private string $pgPublicKey;
    private string $baseUrl;
    private string $serverIp;
    private string $timezone;
    private int $timeout;

    public const SANDBOX_URL = 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/';
    public const PRODUCTION_URL = 'https://api.mynagad.com/api/dfs/';

    public function __construct(
        string $merchantId,
        string $merchantPrivateKey,
        string $pgPublicKey,
        bool $sandbox = false,
        string $serverIp = '127.0.0.1',
        string $timezone = 'Asia/Dhaka',
        int $timeout = 30
    ) {
        $this->merchantId = trim($merchantId);
        $this->merchantPrivateKey = self::formatPrivateKey($merchantPrivateKey);
        $this->pgPublicKey = self::formatPublicKey($pgPublicKey);
        $this->baseUrl = $sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
        $this->serverIp = trim($serverIp);
        $this->timezone = $timezone;
        $this->timeout = $timeout;

        if (empty($this->merchantId)) {
            throw new InvalidArgumentException('Nagad merchantId cannot be empty.');
        }

        date_default_timezone_set($this->timezone);
    }

    // ==========================================
    // Core Payment Workflow Methods
    // ==========================================

    /**
     * Step 1: Initialize checkout with cryptographic challenge
     * 
     * @param string $orderId Unique merchant order/invoice number
     * @param string|null $accountNumber Optional merchant wallet account
     * @return array Contains paymentReferenceId and challenge
     */
    public function initializeCheckout(string $orderId, ?string $accountNumber = null): array
    {
        $dateTime = date('YmdHis');
        $challenge = self::generateChallenge(40);

        $sensitiveData = [
            'merchantId' => $this->merchantId,
            'datetime' => $dateTime,
            'orderId' => $orderId,
            'challenge' => $challenge
        ];

        $sensitiveJson = json_encode($sensitiveData, JSON_UNESCAPED_SLASHES);
        $encryptedSensitiveData = $this->encryptWithPgPublicKey($sensitiveJson);
        $signature = $this->generateSignature($sensitiveJson);

        $payload = [
            'accountNumber' => $accountNumber,
            'dateTime' => $dateTime,
            'sensitiveData' => $encryptedSensitiveData,
            'signature' => $signature
        ];

        $url = $this->baseUrl . "check-out/initialize/{$this->merchantId}/{$orderId}";
        $response = $this->httpPost($url, $payload);

        if (isset($response['reason']) || !isset($response['sensitiveData'])) {
            $msg = $response['message'] ?? 'Nagad Checkout Initialization Failed.';
            throw new RuntimeException("Nagad Init Error: {$msg}");
        }

        $decryptedJson = $this->decryptWithPrivateKey($response['sensitiveData']);
        $decryptedData = json_decode($decryptedJson, true);

        if (!isset($decryptedData['paymentReferenceId'])) {
            throw new RuntimeException("Nagad Init Error: Missing paymentReferenceId in decrypted payload.");
        }

        // Anti-replay challenge check
        if (isset($decryptedData['challenge']) && $decryptedData['challenge'] !== $challenge) {
            throw new RuntimeException("Nagad Security Error: Cryptographic challenge mismatch.");
        }

        return [
            'paymentReferenceId' => $decryptedData['paymentReferenceId'],
            'challenge' => $decryptedData['challenge'] ?? $challenge
        ];
    }

    /**
     * Step 2: Complete checkout and acquire hosted payment redirect URL
     * 
     * @param string $paymentReferenceId From Step 1
     * @param string $orderId Original merchant invoice ID
     * @param float|string $amount Transaction amount
     * @param string $callbackUrl Merchant callback redirect URL
     * @param string $challenge Challenge nonce from Step 1
     * @return array Gateway response containing callBackUrl
     */
    public function completeCheckout(
        string $paymentReferenceId,
        string $orderId,
        $amount,
        string $callbackUrl,
        string $challenge
    ): array {
        $sensitiveDataOrder = [
            'merchantId' => $this->merchantId,
            'orderId' => $orderId,
            'currencyCode' => '050', // ISO 4217 numeric for BDT
            'amount' => number_format((float)$amount, 2, '.', ''),
            'challenge' => $challenge
        ];

        $sensitiveJson = json_encode($sensitiveDataOrder, JSON_UNESCAPED_SLASHES);
        $encryptedSensitiveData = $this->encryptWithPgPublicKey($sensitiveJson);
        $signature = $this->generateSignature($sensitiveJson);

        $payload = [
            'sensitiveData' => $encryptedSensitiveData,
            'signature' => $signature,
            'merchantCallbackURL' => $callbackUrl
        ];

        $url = $this->baseUrl . "check-out/complete/{$paymentReferenceId}";
        $response = $this->httpPost($url, $payload);

        if (!isset($response['status']) || $response['status'] !== 'Success' || empty($response['callBackUrl'])) {
            $msg = $response['message'] ?? json_encode($response);
            throw new RuntimeException("Nagad Checkout Complete Error: {$msg}");
        }

        return $response;
    }

    /**
     * High-level helper: Executes Initialize + Complete in a single call
     * 
     * @return string Hosted payment redirect URL
     */
    public function createPayment(string $orderId, $amount, string $callbackUrl, ?string $accountNumber = null): string
    {
        $init = $this->initializeCheckout($orderId, $accountNumber);
        $complete = $this->completeCheckout(
            $init['paymentReferenceId'],
            $orderId,
            $amount,
            $callbackUrl,
            $init['challenge']
        );

        return $complete['callBackUrl'];
    }

    /**
     * Step 4: Verify payment status directly with Nagad core engine
     * 
     * @param string $paymentRefId Payment reference ID from callback query string
     * @return array Authoritative payment status payload
     */
    public function verifyPayment(string $paymentRefId): array
    {
        $url = $this->baseUrl . "verify/payment/{$paymentRefId}";
        return $this->httpGet($url);
    }

    /**
     * Parse inbound GET callback query string or URL
     */
    public static function parseCallback($callbackInput): array
    {
        if (is_array($callbackInput)) {
            return $callbackInput;
        }

        $parts = parse_url($callbackInput);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            return $query;
        }

        parse_str($callbackInput, $query);
        return $query;
    }

    // ==========================================
    // Cryptographic Engine (OpenSSL PKCS#1 v1.5)
    // ==========================================

    public function encryptWithPgPublicKey(string $plainText): string
    {
        $keyResource = openssl_pkey_get_public($this->pgPublicKey);
        if (!$keyResource) {
            throw new RuntimeException('Invalid Nagad PG Public Key format.');
        }

        $cryptoText = '';
        $ok = openssl_public_encrypt($plainText, $cryptoText, $keyResource, OPENSSL_PKCS1_PADDING);
        if (!$ok) {
            throw new RuntimeException('RSA Public Encryption failed.');
        }

        return base64_encode($cryptoText);
    }

    public function generateSignature(string $plainText): string
    {
        $keyResource = openssl_pkey_get_private($this->merchantPrivateKey);
        if (!$keyResource) {
            throw new RuntimeException('Invalid Merchant Private Key format.');
        }

        $signature = '';
        $ok = openssl_sign($plainText, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            throw new RuntimeException('RSA SHA-256 Signature generation failed.');
        }

        return base64_encode($signature);
    }

    public function decryptWithPrivateKey(string $base64CryptoText): string
    {
        $keyResource = openssl_pkey_get_private($this->merchantPrivateKey);
        if (!$keyResource) {
            throw new RuntimeException('Invalid Merchant Private Key format.');
        }

        $plainText = '';
        $ok = openssl_private_decrypt(base64_decode($base64CryptoText), $plainText, $keyResource, OPENSSL_PKCS1_PADDING);
        if (!$ok) {
            throw new RuntimeException('RSA Private Decryption failed.');
        }

        return $plainText;
    }

    public static function generateChallenge(int $length = 40): string
    {
        return bin2hex(random_bytes((int)($length / 2)));
    }

    private static function formatPrivateKey(string $key): string
    {
        $clean = preg_replace('/-----.*-----|\s+/', '', $key);
        return "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END RSA PRIVATE KEY-----";
    }

    private static function formatPublicKey(string $key): string
    {
        $clean = preg_replace('/-----.*-----|\s+/', '', $key);
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split($clean, 64, "\n") . "-----END PUBLIC KEY-----";
    }

    // ==========================================
    // HTTP Transport Engine (cURL)
    // ==========================================

    private function httpPost(string $url, array $data): array
    {
        $headers = [
            'Content-Type: application/json',
            'X-KM-Api-Version: v-0.2.0',
            'X-KM-IP-V4: ' . $this->serverIp,
            'X-KM-Client-Type: PC_WEB',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_SLASHES));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("Nagad Connection Failure: {$curlError}");
        }

        $decoded = json_decode((string)$response, true);
        if ($decoded === null && !empty($response)) {
            throw new RuntimeException("Invalid JSON response from Nagad (HTTP {$httpCode}): {$response}");
        }

        return $decoded ?? [];
    }

    private function httpGet(string $url): array
    {
        $headers = [
            'X-KM-Api-Version: v-0.2.0',
            'X-KM-IP-V4: ' . $this->serverIp,
            'X-KM-Client-Type: PC_WEB',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("Nagad Connection Failure: {$curlError}");
        }

        $decoded = json_decode((string)$response, true);
        if ($decoded === null && !empty($response)) {
            throw new RuntimeException("Invalid JSON from Nagad Verify (HTTP {$httpCode}): {$response}");
        }

        return $decoded ?? [];
    }
}
