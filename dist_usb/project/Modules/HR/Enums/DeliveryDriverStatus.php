<?php

namespace Modules\HR\Enums;

enum DeliveryDriverStatus: string
{
    case AVAILABLE = 'available';
    case ON_DELIVERY = 'on_delivery';
    case OFFLINE = 'offline';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'متاح',
            self::ON_DELIVERY => 'في توصيل',
            self::OFFLINE => 'خارح الخدمة',
            self::SUSPENDED => 'موقف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::ON_DELIVERY => 'warning',
            self::OFFLINE => 'secondary',
            self::SUSPENDED => 'danger',
        };
    }
}
