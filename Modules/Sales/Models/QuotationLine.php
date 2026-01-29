<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Unit;

/**
 * QuotationLine Model - Line items for quotations
 */
class QuotationLine extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'unit_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'tax_percent',
        'line_total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
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
    // Boot - Auto calculate line total
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($line) {
            // Calculate line total: (qty * price) - discount
            $gross = $line->quantity * $line->unit_price;
            $discount = $gross * ($line->discount_percent / 100);
            $line->line_total = $gross - $discount;
        });
    }
}
