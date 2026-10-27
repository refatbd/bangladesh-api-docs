<?php

namespace Refatbd\Courier\DTO;

class OrderResponse
{
    public bool $isSuccess;
    public ?string $consignmentId;
    public ?string $trackingCode;
    public string $status;
    public float $deliveryFee;
    public ?string $message;
    public array $rawResponse;

    public function __construct(
        bool $isSuccess,
        ?string $consignmentId,
        ?string $trackingCode,
        string $status,
        float $deliveryFee = 0.0,
        ?string $message = null,
        array $rawResponse = []
    ) {
        $this->isSuccess     = $isSuccess;
        $this->consignmentId = $consignmentId;
        $this->trackingCode  = $trackingCode;
        $this->status        = $status;
        $this->deliveryFee   = $deliveryFee;
        $this->message       = $message;
        $this->rawResponse   = $rawResponse;
    }

    public function toArray(): array
    {
        return [
            'success'        => $this->isSuccess,
            'consignment_id' => $this->consignmentId,
            'tracking_code'  => $this->trackingCode,
            'status'         => $this->status,
            'delivery_fee'   => $this->deliveryFee,
            'message'        => $this->message,
            'raw'            => $this->rawResponse,
        ];
    }
}
