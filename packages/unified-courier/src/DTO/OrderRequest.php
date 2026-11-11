<?php

namespace Refatbd\Courier\DTO;

use InvalidArgumentException;

class OrderRequest
{
    public string $invoiceId;
    public string $recipientName;
    public string $recipientPhone;
    public string $recipientAddress;
    public float $amountToCollect; // 0 if prepaid
    public float $weightKg;
    public string $deliveryZone;   // inside_dhaka, dhaka_suburb, outside_dhaka
    public ?string $specialNotes;
    public ?int $storeId;          // Store/Warehouse ID where needed
    public array $extraParams;

    public function __construct(array $attributes)
    {
        $this->invoiceId        = (string)($attributes['invoice_id'] ?? 'INV-' . time());
        $this->recipientName    = (string)($attributes['recipient_name'] ?? '');
        $this->recipientPhone   = (string)($attributes['recipient_phone'] ?? '');
        $this->recipientAddress = (string)($attributes['recipient_address'] ?? '');
        $this->amountToCollect  = (float)($attributes['amount_to_collect'] ?? 0.0);
        $this->weightKg         = (float)($attributes['weight_kg'] ?? 1.0);
        $this->deliveryZone     = (string)($attributes['delivery_zone'] ?? 'inside_dhaka');
        $this->specialNotes     = $attributes['special_notes'] ?? null;
        $this->storeId          = isset($attributes['store_id']) ? (int)$attributes['store_id'] : null;
        $this->extraParams      = $attributes['extra_params'] ?? [];

        $this->validate();
    }

    protected function validate(): void
    {
        if (empty($this->recipientName)) {
            throw new InvalidArgumentException('recipient_name cannot be empty.');
        }

        // Clean BD phone number (normalize 880 or +880 to 11 digits)
        $cleanPhone = preg_replace('/^(?:\+880|880)/', '0', $this->recipientPhone);
        if (!preg_match('/^01[3-9]\d{8}$/', $cleanPhone)) {
            throw new InvalidArgumentException("Invalid Bangladeshi mobile number: '{$this->recipientPhone}'. Must be 11 digits starting with 01[3-9].");
        }
        $this->recipientPhone = $cleanPhone;

        if (empty($this->recipientAddress)) {
            throw new InvalidArgumentException('recipient_address cannot be empty.');
        }

        if ($this->weightKg <= 0) {
            $this->weightKg = 0.5;
        }
    }

    /**
     * Get weight in grams for couriers requiring integer gram format (RedX, Paperfly).
     *
     * @return int
     */
    public function getWeightInGrams(): int
    {
        return (int)round($this->weightKg * 1000);
    }
}
