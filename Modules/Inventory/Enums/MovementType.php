<?php

namespace Modules\Inventory\Enums;

/**
 * MovementType Enum - Types of stock movements
 */
enum MovementType: string
{
    case PURCHASE = 'purchase';           // Stock received from purchase
    case SALE = 'sale';                   // Stock sold to customer
    case ADJUSTMENT_IN = 'adjustment_in'; // Manual increase
    case ADJUSTMENT_OUT = 'adjustment_out'; // Manual decrease
    case TRANSFER_IN = 'transfer_in';     // Transfer received from another warehouse
    case TRANSFER_OUT = 'transfer_out';   // Transfer sent to another warehouse
    case RETURN_IN = 'return_in';         // Customer return received
    case RETURN_OUT = 'return_out';       // Return to supplier
    case INITIAL = 'initial';             // Opening balance

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase Receipt',
            self::SALE => 'Sales Issue',
            self::ADJUSTMENT_IN => 'Adjustment In',
            self::ADJUSTMENT_OUT => 'Adjustment Out',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
            self::RETURN_IN => 'Customer Return',
            self::RETURN_OUT => 'Supplier Return',
            self::INITIAL => 'Initial Stock',
        };
    }

    /**
     * Does this movement increase stock?
     */
    public function isInward(): bool
    {
        return in_array($this, [
            self::PURCHASE,
            self::ADJUSTMENT_IN,
            self::TRANSFER_IN,
            self::RETURN_IN,
            self::INITIAL,
        ]);
    }

    /**
     * Does this movement decrease stock?
     */
    public function isOutward(): bool
    {
        return !$this->isInward();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
