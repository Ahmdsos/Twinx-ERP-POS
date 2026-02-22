<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\StockMovement;

/**
 * GrnLine Model - GRN Line Items
 */
class GrnLine extends Model
{
    protected $fillable = [
        'grn_id',
        'purchase_order_line_id',
        'product_id',
        'quantity',
        'unit_cost',
        'line_total',
        'stock_movement_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'line_total' => 'decimal:2',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function purchaseOrderLine(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    // ========================================
    // Events
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            $line->line_total = $line->quantity * $line->unit_cost;
        });
    }
}
