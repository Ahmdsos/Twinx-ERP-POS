<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ProductBatch Model
 * For tracking batch/lot numbers and expiry dates
 */
class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'lot_number',
        'manufacturing_date',
        'expiry_date',
        'quantity',
        'unit_cost',
        'supplier_batch',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class, 'batch_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expiry_date)
            return false;

        $warningDays = $this->product?->expiry_warning_days ?? 30;
        return $this->expiry_date->isBetween(now(), now()->addDays($warningDays));
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date)
            return null;
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryStatusAttribute(): string
    {
        if ($this->isExpired)
            return 'expired';
        if ($this->isExpiringSoon)
            return 'warning';
        return 'ok';
    }
}
