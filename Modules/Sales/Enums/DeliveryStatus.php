<?php

namespace Modules\Sales\Enums;

/**
 * DeliveryStatus - Status of delivery orders
 */
enum DeliveryStatus: string
{
    case DRAFT = 'draft';
    case READY = 'ready';           // Ready to ship
    case SHIPPED = 'shipped';       // Out for delivery
    case DELIVERED = 'delivered';   // Completed
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::READY => 'Ready to Ship',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function isCompleted(): bool
    {
        return $this === self::DELIVERED;
    }
}
