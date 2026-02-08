<?php

namespace Modules\HR\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case ABSENT = 'absent';
    case LATE = 'late';
    case ON_LEAVE = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'حاضر',
            self::ABSENT => 'غائب',
            self::LATE => 'متأخر',
            self::ON_LEAVE => 'إجازة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PRESENT => 'success',
            self::ABSENT => 'danger',
            self::LATE => 'warning',
            self::ON_LEAVE => 'info',
        };
    }
}
