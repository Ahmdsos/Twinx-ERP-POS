<?php

namespace Modules\Finance\Enums;

/**
 * Treasury Transaction Type Enum
 * TRUTH: Only two transaction types exist
 */
enum TreasuryTransactionType: string
{
    case RECEIPT = 'receipt';
    case PAYMENT = 'payment';

    public function label(): string
    {
        return match ($this) {
            self::RECEIPT => 'إيصال قبض',
            self::PAYMENT => 'إيصال صرف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RECEIPT => 'success',
            self::PAYMENT => 'danger',
        };
    }
}
