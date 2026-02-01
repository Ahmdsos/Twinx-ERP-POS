<?php

namespace Modules\Sales\Enums;

enum CustomerType: string
{
    case INDIVIDUAL = 'individual';
    case BUSINESS = 'business';
    case WHOLESALE = 'wholesale';
    case HALF_WHOLESALE = 'half_wholesale'; // Existing data support
    case VIP = 'vip';

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'أفراد',
            self::BUSINESS => 'شركات',
            self::WHOLESALE => 'جملة',
            self::HALF_WHOLESALE => 'نصف جملة',
            self::VIP => 'عميل مميز',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'info',
            self::BUSINESS => 'primary',
            self::WHOLESALE => 'warning',
            self::HALF_WHOLESALE => 'secondary',
            self::VIP => 'success',
        };
    }
}
