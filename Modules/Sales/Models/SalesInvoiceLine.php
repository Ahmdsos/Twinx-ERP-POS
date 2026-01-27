<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Models\Product;
use Modules\Accounting\Models\Account;

/**
 * SalesInvoiceLine Model
 */
class SalesInvoiceLine extends Model
{
    protected $fillable = [
        'sales_invoice_id',
        'product_id',
        'description',
        'account_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
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

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
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
            $line->salesInvoice->recalculateTotals();
        });

        static::deleted(function ($line) {
            $line->salesInvoice->recalculateTotals();
        });
    }

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
}
