<?php

namespace Refatbd\Courier\Enums;

class CourierStatus
{
    public const PENDING     = 'pending';
    public const IN_TRANSIT  = 'in_transit';
    public const DELIVERED   = 'delivered';
    public const CANCELLED   = 'cancelled';
    public const RETURNED    = 'returned';
    public const UNKNOWN     = 'unknown';

    /**
     * Normalize fluctuating provider status strings to canonical status.
     *
     * @param string $provider
     * @param string $rawStatus
     * @return string
     */
    public static function normalize(string $provider, string $rawStatus): string
    {
        $raw = strtolower(trim($rawStatus));

        // Steadfast normalization
        if ($provider === 'steadfast') {
            if (in_array($raw, ['in_review', 'pending'])) return self::PENDING;
            if (in_array($raw, ['in_transit', 'picked_up', 'out_for_delivery'])) return self::IN_TRANSIT;
            if (in_array($raw, ['delivered', 'delivered_approval_pending'])) return self::DELIVERED;
            if (in_array($raw, ['cancelled', 'cancelled_by_merchant'])) return self::CANCELLED;
            if (in_array($raw, ['returned', 'return_in_transit', 'partial_delivered'])) return self::RETURNED;
        }

        // RedX normalization
        if ($provider === 'redx') {
            if (in_array($raw, ['order_placed', 'pickup_requested', 'pickup_pending'])) return self::PENDING;
            if (in_array($raw, ['picked_up', 'in_transit', 'out_for_delivery', 'reached_sorting_hub'])) return self::IN_TRANSIT;
            if (in_array($raw, ['delivered', 'delivered_partial'])) return self::DELIVERED;
            if (in_array($raw, ['order_cancelled', 'pickup_cancelled'])) return self::CANCELLED;
            if (in_array($raw, ['returning_to_merchant', 'returned'])) return self::RETURNED;
        }

        // Pathao normalization
        if ($provider === 'pathao') {
            if (in_array($raw, ['pending', 'assigned_for_pickup', 'pickup_in_progress'])) return self::PENDING;
            if (in_array($raw, ['picked_up', 'in_transit', 'dispatched', 'out_for_delivery'])) return self::IN_TRANSIT;
            if ($raw === 'delivered') return self::DELIVERED;
            if (in_array($raw, ['cancelled', 'pickup_failed'])) return self::CANCELLED;
            if (in_array($raw, ['return_in_transit', 'returned_to_merchant'])) return self::RETURNED;
        }

        // Paperfly normalization
        if ($provider === 'paperfly') {
            if (in_array($raw, ['order_placed', 'pickup_requested'])) return self::PENDING;
            if (in_array($raw, ['picked_up', 'in_transit', 'out_for_delivery'])) return self::IN_TRANSIT;
            if ($raw === 'delivered') return self::DELIVERED;
            if (in_array($raw, ['order_cancelled', 'cancelled'])) return self::CANCELLED;
            if (in_array($raw, ['returned_to_hub', 'returned_to_merchant', 'rejected'])) return self::RETURNED;
        }

        // Carrybee normalization
        if ($provider === 'carrybee') {
            if (in_array($raw, ['order_placed', 'pickup_scheduled'])) return self::PENDING;
            if (in_array($raw, ['picked_up', 'in_transit', 'out_for_delivery'])) return self::IN_TRANSIT;
            if ($raw === 'delivered') return self::DELIVERED;
            if ($raw === 'cancelled') return self::CANCELLED;
            if (in_array($raw, ['returned', 'returned_to_merchant'])) return self::RETURNED;
        }

        // eCourier normalization
        if ($provider === 'ecourier') {
            if (in_array($raw, ['pending', 'order_placed', 'pickup_pending'])) return self::PENDING;
            if (in_array($raw, ['in_transit', 'hub_transit', 'dispatch', 'rider_assigned'])) return self::IN_TRANSIT;
            if (in_array($raw, ['delivered', 'delivered_paid'])) return self::DELIVERED;
            if ($raw === 'cancelled') return self::CANCELLED;
            if (in_array($raw, ['returned', 'return_received'])) return self::RETURNED;
        }

        return self::UNKNOWN;
    }
}
