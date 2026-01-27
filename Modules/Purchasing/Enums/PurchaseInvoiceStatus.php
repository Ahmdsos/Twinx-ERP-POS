<?php

namespace Modules\Purchasing\Enums;

/**
 * PurchaseInvoiceStatus - Status of purchase invoices (bills)
 */
enum PurchaseInvoiceStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';   // Awaiting payment
    case PARTIAL = 'partial';   // Partially paid
    case PAID = 'paid';         // Fully paid
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Payment',
            self::PARTIAL => 'Partially Paid',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function canPay(): bool
    {
        return in_array($this, [self::PENDING, self::PARTIAL]);
    }
}
