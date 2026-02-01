<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Enums\MovementType;
use Modules\Core\Traits\HasDocumentNumber;

/**
 * StockMovement Model - Inventory Transaction History
 * 
 * Records all stock movements for audit and FIFO costing.
 */
class StockMovement extends Model
{
    use HasFactory, HasDocumentNumber;

    protected $fillable = [
        'movement_number',
        'movement_date',
        'type',
        'product_id',
        'warehouse_id',
        'to_warehouse_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'remaining_quantity',
        'source_type',
        'source_id',
        'reference',
        'notes',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'datetime',
        'type' => MovementType::class,
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'remaining_quantity' => 'decimal:4',
    ];

    /**
     * Document number settings
     */
    public function getDocumentPrefix(): string
    {
        return 'SM';
    }

    public function getDocumentNumberField(): string
    {
        return 'movement_number';
    }

    // =====================
    // Relationships
    // =====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(\Modules\Accounting\Models\JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the source document (polymorphic)
     */
    public function source()
    {
        if ($this->source_type && $this->source_id) {
            return $this->morphTo('source', 'source_type', 'source_id');
        }
        return null;
    }

    // =====================
    // Scopes
    // =====================

    public function scopeInward($query)
    {
        return $query->whereIn('type', [
            MovementType::PURCHASE->value,
            MovementType::ADJUSTMENT_IN->value,
            MovementType::TRANSFER_IN->value,
            MovementType::RETURN_IN->value,
            MovementType::INITIAL->value,
        ]);
    }

    public function scopeOutward($query)
    {
        return $query->whereIn('type', [
            MovementType::SALE->value,
            MovementType::ADJUSTMENT_OUT->value,
            MovementType::TRANSFER_OUT->value,
            MovementType::RETURN_OUT->value,
        ]);
    }

    public function scopeWithRemainingStock($query)
    {
        return $query->where('remaining_quantity', '>', 0);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeInWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // =====================
    // Helpers
    // =====================

    public function isInward(): bool
    {
        return $this->type->isInward();
    }

    public function isOutward(): bool
    {
        return $this->type->isOutward();
    }

    /**
     * Consume quantity from this movement (for FIFO)
     */
    public function consume(float $quantity): float
    {
        $consumed = min($quantity, $this->remaining_quantity);
        $this->remaining_quantity -= $consumed;
        $this->save();
        return $consumed;
    }
}
