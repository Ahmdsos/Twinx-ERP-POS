<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Modules\Inventory\Models\Product;
use Modules\Sales\Models\PosShift;

/**
 * PriceOverrideLog - Tracks all price modifications in POS
 * 
 * Security feature to audit price changes with manager approval
 */
class PriceOverrideLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'shift_id',
        'product_id',
        'original_price',
        'override_price',
        'discount_percent',
        'reason',
        'approval_method',
        'is_approved',
        'ip_address',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'override_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(PosShift::class, 'shift_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==================== STATIC METHODS ====================

    /**
     * Log a price override attempt
     */
    public static function logOverride(array $data): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'manager_id' => $data['manager_id'] ?? null,
            'shift_id' => $data['shift_id'] ?? null,
            'product_id' => $data['product_id'],
            'original_price' => $data['original_price'],
            'override_price' => $data['override_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'reason' => $data['reason'] ?? null,
            'approval_method' => $data['approval_method'] ?? 'pin',
            'is_approved' => $data['is_approved'] ?? false,
            'ip_address' => request()->ip(),
        ]);
    }

    // ==================== SCOPES ====================

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeRejected($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeForShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    // ==================== ACCESSORS ====================

    public function getPriceChangeAttribute(): float
    {
        return $this->override_price - $this->original_price;
    }

    public function getPriceChangePercentAttribute(): float
    {
        if ($this->original_price == 0)
            return 0;
        return (($this->override_price - $this->original_price) / $this->original_price) * 100;
    }
}
