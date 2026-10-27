<?php

namespace Refatbd\Courier\DTO;

class TrackingResponse
{
    public string $trackingCode;
    public string $status;
    public ?string $currentLocation;
    public ?string $recipientPhone;
    public array $milestones;
    public string $updatedAt;
    public array $raw;

    public function __construct(
        string $trackingCode,
        string $status,
        ?string $currentLocation = null,
        ?string $recipientPhone = null,
        array $milestones = [],
        ?string $updatedAt = null,
        array $raw = []
    ) {
        $this->trackingCode    = $trackingCode;
        $this->status          = $status;
        $this->currentLocation = $currentLocation;
        $this->recipientPhone  = $recipientPhone;
        $this->milestones      = $milestones;
        $this->updatedAt       = $updatedAt ?? date('c');
        $this->raw             = $raw;
    }
}
