<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Unit;

/**
 * SalesOrderLine Model - SO Line Items
 */
class SalesOrderLine extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'delivered_quantity',
        'invoiced_quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'line_total',
        'unit_id',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'delivered_quantity' => 'decimal:4',
        'invoiced_quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // ========================================
    // Events
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            $line->calculateTotals();
        });

        static::saved(function ($line) {
            $line->salesOrder->recalculateTotals();
        });

        static::deleted(function ($line) {
            $line->salesOrder->recalculateTotals();
        });
    }

    // ========================================
    // Business Methods
    // ========================================

    public function calculateTotals(): void
    {
        $subtotal = $this->quantity * $this->unit_price;

        if ($this->discount_percent > 0) {
            $this->discount_amount = $subtotal * ($this->discount_percent / 100);
        }
        $afterDiscount = $subtotal - $this->discount_amount;

        if ($this->tax_percent > 0) {
            $this->tax_amount = $afterDiscount * ($this->tax_percent / 100);
        }

        $this->line_total = $afterDiscount + $this->tax_amount;
    }

    public function getRemainingToDeliver(): float
    {
        return max(0, $this->quantity - $this->delivered_quantity);
    }

    public function getRemainingToInvoice(): float
    {
        return max(0, $this->delivered_quantity - $this->invoiced_quantity);
    }

    public function isFullyDelivered(): bool
    {
        return $this->delivered_quantity >= $this->quantity;
    }

    public function addDeliveredQuantity(float $qty): void
    {
        $this->increment('delivered_quantity', $qty);
        $this->salesOrder->updateDeliveryStatus();
    }

    public function addInvoicedQuantity(float $qty): void
    {
        $this->increment('invoiced_quantity', $qty);
    }
}
