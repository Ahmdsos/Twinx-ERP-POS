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
            self::DRAFT => 'مسودة',
            self::CONFIRMED => 'مؤكد',
            self::PROCESSING => 'قيد التنفيذ',
            self::PARTIAL => 'تسليم جزئي',
            self::DELIVERED => 'تم التسليم',
            self::INVOICED => 'تمت الفوترة',
            self::CANCELLED => 'ملغي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::CONFIRMED => 'info',
            self::PROCESSING => 'primary',
            self::PARTIAL => 'warning',
            self::DELIVERED => 'success',
            self::INVOICED => 'purple',
            self::CANCELLED => 'danger',
        };
    }

    public function badgeClass(): string
    {
        $color = $this->color();
        // Warning and Info usually need dark text for better contrast
        if (in_array($color, ['warning', 'info', 'secondary'])) {
            return 'bg-' . $color . ' bg-opacity-10 text-' . $color;
        }

        return 'bg-' . $color . ' text-white';
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
