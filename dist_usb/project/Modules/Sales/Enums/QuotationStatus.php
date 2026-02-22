<?php

namespace Modules\Sales\Enums;

/**
 * QuotationStatus - Status of Sales Quotations
 */
enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CONVERTED = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::SENT => 'مرسل',
            self::ACCEPTED => 'مقبول',
            self::REJECTED => 'مرفوض',
            self::EXPIRED => 'منتهي الصلاحية',
            self::CONVERTED => 'تم التحويل',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::SENT => 'info',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
            self::EXPIRED => 'warning',
            self::CONVERTED => 'primary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'bi-file-earmark',
            self::SENT => 'bi-send',
            self::ACCEPTED => 'bi-check-circle',
            self::REJECTED => 'bi-x-circle',
            self::EXPIRED => 'bi-clock-history',
            self::CONVERTED => 'bi-arrow-right-circle',
        };
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canConvert(): bool
    {
        return $this === self::ACCEPTED;
    }
    public function badgeClass(): string
    {
        $color = $this->color();
        // Light backgrounds for specific statuses
        if (in_array($color, ['warning', 'info', 'secondary'])) {
            return 'bg-' . $color . ' bg-opacity-10 text-' . $color;
        }
        return 'bg-' . $color . ' text-white';
    }
}
