<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Modules\Sales\Models\Customer;
use Modules\Inventory\Models\Warehouse;

/**
 * PosHeldSale Model
 * 
 * Represents a "parked" or "held" sale in the POS system.
 * Replaces session storage with proper DB persistence.
 */
class PosHeldSale extends Model
{
    protected $fillable = [
        'hold_number',
        'user_id',
        'customer_id',
        'warehouse_id',
        'items',
        'subtotal',
        'tax',
        'total',
        'notes',
        'status',
        'resumed_at',
        'resumed_by',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'resumed_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function resumedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resumed_by');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeHeld($query)
    {
        return $query->where('status', 'held');
    }

    public function scopeForUser($query, $userId = null)
    {
        return $query->where('user_id', $userId ?? auth()->id());
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Generate unique hold number with row lock
     */
    public static function generateHoldNumber(): string
    {
        $last = self::orderByDesc('id')->lockForUpdate()->first();
        $nextNumber = $last ? ((int) substr($last->hold_number, 5)) + 1 : 1;
        return 'HOLD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new held sale
     */
    public static function hold(array $data): self
    {
        return self::create([
            'hold_number' => self::generateHoldNumber(),
            'user_id' => auth()->id(),
            'customer_id' => $data['customer_id'] ?? null,
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'items' => $data['items'],
            'subtotal' => $data['subtotal'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'total' => $data['total'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'status' => 'held',
        ]);
    }

    /**
     * Resume this held sale (mark as resumed)
     */
    public function resume(): self
    {
        $this->update([
            'status' => 'resumed',
            'resumed_at' => now(),
            'resumed_by' => auth()->id(),
        ]);

        return $this;
    }
}
