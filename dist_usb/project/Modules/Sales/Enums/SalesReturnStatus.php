<?php

namespace Modules\Sales\Enums;

enum SalesReturnStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case COMPLETED = 'completed'; // Stock returned & Credit Note created
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::APPROVED => 'معتمد',
            self::COMPLETED => 'مكتمل',
            self::REJECTED => 'مرفوض',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::APPROVED => 'primary',
            self::COMPLETED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
