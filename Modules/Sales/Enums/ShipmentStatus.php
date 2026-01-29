<?php

namespace Modules\Sales\Enums;

/**
 * ShipmentStatus - حالة الشحنة
 */
enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case PICKED_UP = 'picked_up';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'في انتظار التسليم',
            self::PICKED_UP => 'تم الاستلام من المخزن',
            self::IN_TRANSIT => 'في الطريق',
            self::OUT_FOR_DELIVERY => 'خرج للتسليم',
            self::DELIVERED => 'تم التسليم',
            self::FAILED => 'فشل التسليم',
            self::RETURNED => 'مرتجع',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'secondary',
            self::PICKED_UP => 'info',
            self::IN_TRANSIT => 'primary',
            self::OUT_FOR_DELIVERY => 'warning',
            self::DELIVERED => 'success',
            self::FAILED => 'danger',
            self::RETURNED => 'dark',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'bi-hourglass-split',
            self::PICKED_UP => 'bi-box-seam',
            self::IN_TRANSIT => 'bi-truck',
            self::OUT_FOR_DELIVERY => 'bi-geo-alt',
            self::DELIVERED => 'bi-check-circle',
            self::FAILED => 'bi-x-circle',
            self::RETURNED => 'bi-arrow-counterclockwise',
        };
    }
}
