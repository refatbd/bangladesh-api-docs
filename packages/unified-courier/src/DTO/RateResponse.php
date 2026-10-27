<?php

namespace Refatbd\Courier\DTO;

class RateResponse
{
    public float $baseCharge;
    public float $weightExtra;
    public float $codCharge;
    public float $totalCharge;
    public string $estimatedSla;
    public array $raw;

    public function __construct(
        float $baseCharge,
        float $weightExtra,
        float $codCharge,
        float $totalCharge,
        string $estimatedSla = '24-72 Hours',
        array $raw = []
    ) {
        $this->baseCharge   = $baseCharge;
        $this->weightExtra  = $weightExtra;
        $this->codCharge    = $codCharge;
        $this->totalCharge  = $totalCharge;
        $this->estimatedSla = $estimatedSla;
        $this->raw          = $raw;
    }
}
