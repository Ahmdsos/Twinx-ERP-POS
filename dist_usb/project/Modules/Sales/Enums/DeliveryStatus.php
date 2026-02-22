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
    case RETURNED = 'returned';     // Failed/Returned
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::READY => 'جاهز للشحن',
            self::SHIPPED => 'تم الشحن',
            self::DELIVERED => 'تم التسليم',
            self::RETURNED => 'مرتجع/فشل',
            self::CANCELLED => 'ملغي',
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
