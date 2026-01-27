<?php

namespace Modules\Purchasing\Enums;

/**
 * PurchaseOrderStatus - Status of purchase orders
 */
enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';       // Awaiting approval
    case APPROVED = 'approved';     // Ready to send to supplier
    case SENT = 'sent';             // Sent to supplier
    case PARTIAL = 'partial';       // Partially received
    case RECEIVED = 'received';     // Fully received
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::SENT => 'Sent to Supplier',
            self::PARTIAL => 'Partially Received',
            self::RECEIVED => 'Fully Received',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING]);
    }

    public function canReceive(): bool
    {
        return in_array($this, [self::APPROVED, self::SENT, self::PARTIAL]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING, self::APPROVED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
