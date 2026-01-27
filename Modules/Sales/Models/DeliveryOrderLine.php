<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\StockMovement;

/**
 * DeliveryOrderLine Model - DO Line Items
 */
class DeliveryOrderLine extends Model
{
    protected $fillable = [
        'delivery_order_id',
        'sales_order_line_id',
        'product_id',
        'quantity',
        'unit_cost',
        'line_cost',
        'stock_movement_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'line_cost' => 'decimal:2',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function salesOrderLine(): BelongsTo
    {
        return $this->belongsTo(SalesOrderLine::class);
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
            $line->line_cost = $line->quantity * $line->unit_cost;
        });
    }
}
