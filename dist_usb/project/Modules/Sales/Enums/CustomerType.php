<?php

namespace Modules\Sales\Enums;

enum CustomerType: string
{
    case CONSUMER = 'consumer';
    case COMPANY = 'company';
    case DISTRIBUTOR = 'distributor';
    case WHOLESALE = 'wholesale';
    case HALF_WHOLESALE = 'half_wholesale';
    case QUARTER_WHOLESALE = 'quarter_wholesale';
    case TECHNICIAN = 'technician';
    case EMPLOYEE = 'employee';
    case VIP = 'vip';

    public function label(): string
    {
        return match ($this) {
            self::CONSUMER => 'فرد (Consumer)',
            self::COMPANY => 'شركة (Company)',
            self::DISTRIBUTOR => 'موزع معتمد (Distributor)',
            self::WHOLESALE => 'تاجر جملة (Wholesale)',
            self::HALF_WHOLESALE => 'نص جملة (Half Wholesale)',
            self::QUARTER_WHOLESALE => 'ربع جملة (Quarter Wholesale)',
            self::TECHNICIAN => 'فني / مقاول (Technician)',
            self::EMPLOYEE => 'موظف (Employee)',
            self::VIP => 'عميل مميز (VIP)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CONSUMER => 'info',
            self::COMPANY => 'primary',
            self::DISTRIBUTOR => 'success',
            self::WHOLESALE => 'warning',
            self::HALF_WHOLESALE => 'secondary',
            self::QUARTER_WHOLESALE => 'dark',
            self::TECHNICIAN => 'danger',
            self::EMPLOYEE => 'light',
            self::VIP => 'success',
        };
    }
}
