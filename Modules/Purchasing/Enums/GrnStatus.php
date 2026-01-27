<?php

namespace Modules\Purchasing\Enums;

/**
 * GrnStatus - Status of Goods Received Notes
 */
enum GrnStatus: string
{
    case DRAFT = 'draft';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }
}
