<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sales\Models\Customer;

/**
 * Loyalty Transaction Model
 */
class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'points',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get type label in Arabic
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'earn' => 'اكتساب',
            'redeem' => 'استبدال',
            'expire' => 'انتهاء صلاحية',
            'adjust' => 'تعديل',
            default => $this->type,
        };
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'earn' => 'bg-success',
            'redeem' => 'bg-primary',
            'expire' => 'bg-warning text-dark',
            'adjust' => 'bg-info',
            default => 'bg-secondary',
        };
    }
}
