<?php

namespace Refatbd\Courier\Contracts;

use Refatbd\Courier\DTO\OrderRequest;
use Refatbd\Courier\DTO\OrderResponse;
use Refatbd\Courier\DTO\TrackingResponse;
use Refatbd\Courier\DTO\RateResponse;
use Refatbd\Courier\DTO\WebhookEvent;

interface CourierAdapterInterface
{
    /**
     * Create/book a new consignment parcel.
     *
     * @param OrderRequest $request
     * @return OrderResponse
     */
    public function createOrder(OrderRequest $request): OrderResponse;

    /**
     * Query real-time tracking milestones and current status.
     *
     * @param string $trackingCode
     * @return TrackingResponse
     */
    public function trackOrder(string $trackingCode): TrackingResponse;

    /**
     * Cancel an active parcel before rider pickup.
     *
     * @param string $trackingCode
     * @return bool
     */
    public function cancelOrder(string $trackingCode): bool;

    /**
     * Estimate shipping and COD charges dynamically.
     *
     * @param float $weightKg
     * @param string $zone
     * @param float $codAmount
     * @return RateResponse
     */
    public function calculateRate(float $weightKg, string $zone, float $codAmount = 0): RateResponse;

    /**
     * Parse inbound webhook request and verify provider signatures.
     *
     * @param array $payload
     * @param array $headers
     * @return WebhookEvent
     */
    public function handleWebhook(array $payload, array $headers = []): WebhookEvent;
}
