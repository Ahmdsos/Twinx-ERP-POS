<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Unit;

/**
 * PurchaseOrderLine Model - PO Line Items
 */
class PurchaseOrderLine extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'received_quantity',
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
        'received_quantity' => 'decimal:4',
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

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
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
            $line->purchaseOrder->recalculateTotals();
        });

        static::deleted(function ($line) {
            $line->purchaseOrder->recalculateTotals();
        });
    }

    // ========================================
    // Business Methods
    // ========================================

    public function calculateTotals(): void
    {
        $subtotal = $this->quantity * $this->unit_price;

        // Calculate discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = $subtotal * ($this->discount_percent / 100);
        }
        $afterDiscount = $subtotal - $this->discount_amount;

        // Calculate tax
        if ($this->tax_percent > 0) {
            $this->tax_amount = $afterDiscount * ($this->tax_percent / 100);
        }

        $this->line_total = $afterDiscount + $this->tax_amount;
    }

    public function getRemainingQuantity(): float
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    public function addReceivedQuantity(float $qty): void
    {
        $this->increment('received_quantity', $qty);
        $this->purchaseOrder->updateReceiptStatus();
    }
}
