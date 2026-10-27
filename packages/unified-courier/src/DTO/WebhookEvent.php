<?php

namespace Refatbd\Courier\DTO;

class WebhookEvent
{
    public string $provider;
    public string $eventName;
    public string $trackingCode;
    public ?string $consignmentId;
    public string $status;
    public array $payload;
    public string $timestamp;

    public function __construct(
        string $provider,
        string $eventName,
        string $trackingCode,
        ?string $consignmentId,
        string $status,
        array $payload = []
    ) {
        $this->provider      = $provider;
        $this->eventName     = $eventName;
        $this->trackingCode  = $trackingCode;
        $this->consignmentId = $consignmentId;
        $this->status        = $status;
        $this->payload       = $payload;
        $this->timestamp     = date('c');
    }

    /**
     * Generate the provider-mandated handshake HTTP response.
     * (E.g. Carrybee strictly requires HTTP 202 {"message": "received"} to prevent automatic webhook disabling).
     *
     * @return array
     */
    public function respond(): array
    {
        if ($this->provider === 'carrybee') {
            return [
                'status_code' => 202,
                'headers'     => ['Content-Type' => 'application/json'],
                'body'        => json_encode(['message' => 'received', 'status' => 'success']),
            ];
        }

        return [
            'status_code' => 200,
            'headers'     => ['Content-Type' => 'application/json'],
            'body'        => json_encode(['status' => 'success', 'acknowledged' => true]),
        ];
    }
}
