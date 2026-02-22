<?php

namespace Modules\Accounting\Enums;

/**
 * JournalStatus Enum - Status of journal entries
 */
enum JournalStatus: string
{
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case REVERSED = 'reversed';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::POSTED => 'Posted',
            self::REVERSED => 'Reversed',
        };
    }

    /**
     * Check if this status allows editing
     */
    public function isEditable(): bool
    {
        return $this === self::DRAFT;
    }

    /**
     * Check if this status affects ledger
     */
    public function affectsLedger(): bool
    {
        return in_array($this, [self::POSTED, self::REVERSED]);
    }

    /**
     * Get all values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
