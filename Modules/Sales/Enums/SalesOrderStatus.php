<?php

namespace Modules\Sales\Enums;

/**
 * SalesOrderStatus - Status of sales orders
 */
enum SalesOrderStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case PARTIAL = 'partial';       // Partially delivered
    case DELIVERED = 'delivered';   // Fully delivered
    case INVOICED = 'invoiced';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::CONFIRMED => 'Confirmed',
            self::PROCESSING => 'Processing',
            self::PARTIAL => 'Partially Delivered',
            self::DELIVERED => 'Delivered',
            self::INVOICED => 'Invoiced',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT]);
    }

    public function canDeliver(): bool
    {
        return in_array($this, [self::CONFIRMED, self::PROCESSING, self::PARTIAL]);
    }

    public function canInvoice(): bool
    {
        return in_array($this, [self::DELIVERED, self::PARTIAL]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::CONFIRMED]);
    }
}
