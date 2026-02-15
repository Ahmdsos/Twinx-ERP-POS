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
    use \Modules\Core\Traits\HasTaxCalculations;
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

    // Compatibility alias: Some legacy code or internal logic expects invoice()
    public function invoice(): BelongsTo
    {
        return $this->salesInvoice();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
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
        $calc = $this->calculateLineFromNet(
            (float) $this->quantity,
            (float) $this->unit_price,
            (float) ($this->discount_amount ?? 0),
            $this->tax_percent
        );

        $this->tax_amount = $calc['tax_amount'];
        $this->line_total = $calc['line_total'];
    }
}
