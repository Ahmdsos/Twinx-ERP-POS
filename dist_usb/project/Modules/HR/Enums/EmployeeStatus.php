<?php

namespace Modules\HR\Enums;

/**
 * EmployeeStatus Enum
 * H-16 FIX: Replaced CONST strings with proper PHP Enum
 */
enum EmployeeStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ON_LEAVE = 'on_leave';
    case TERMINATED = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'نشط',
            self::INACTIVE => 'غير نشط',
            self::ON_LEAVE => 'في إجازة',
            self::TERMINATED => 'تم إنهاء الخدمة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::ON_LEAVE => 'warning text-dark',
            self::TERMINATED => 'danger',
        };
    }
}
