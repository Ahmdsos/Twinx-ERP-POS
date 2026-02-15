<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Sales\Enums\SalesInvoiceStatus;
use Modules\Accounting\Models\JournalEntry;

/**
 * SalesInvoice Model
 */
class SalesInvoice extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sales_order_id',
        'delivery_order_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'balance_due',
        'currency',
        'exchange_rate',
        'notes',
        'terms',
        'journal_entry_id',
        'is_delivery',
        'delivery_fee',
        'driver_id',
        'delivery_status',
        'shipping_address',
        'warehouse_id',
        'pos_shift_id',
        'created_by', // Phase 3: Allow cashier assignment
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'status' => SalesInvoiceStatus::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    /**
     * Virtual Attributes for SSOT Reporting
     */

    /**
     * Net Revenue: Subtotal minus global discount. 
     * This excluding tax and delivery regardless of tax_inclusive setting.
     */
    public function getNetRevenueAttribute(): float
    {
        return (float) (($this->subtotal ?? 0) - ($this->discount_amount ?? 0));
    }

    /**
     * Gross Total: The actual amount the customer pays (Total).
     */
    public function getGrossTotalAttribute(): float
    {
        return (float) ($this->total ?? 0);
    }

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        return \App\Models\Setting::getValue('invoice_prefix', 'INV');
    }

    public function getDocumentNumberField(): string
    {
        return 'invoice_number';
    }

    // ========================================
    // Boot Events
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($invoice) {
            // Phase 2.3: due_date must be >= invoice_date
            if ($invoice->due_date && $invoice->invoice_date && $invoice->due_date < $invoice->invoice_date) {
                throw new \RuntimeException("تاريخ الاستحقاق لا يمكن أن يكون قبل تاريخ الفاتورة");
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesInvoiceLine::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            SalesInvoiceStatus::PENDING,
            SalesInvoiceStatus::PARTIAL,
        ]);
    }

    public function scopeOverdue($query)
    {
        return $query->pending()
            ->where('due_date', '<', now()->toDateString());
    }

    // ========================================
    // Business Methods
    // ========================================

    public function recalculateTotals(): void
    {
        $grossTotal = $this->lines()->sum('line_total');
        $taxAmount = $this->lines()->sum('tax_amount');
        $discountAmount = $this->lines()->sum('discount_amount'); // Line level discounts?

        // If discount_amount column on Invoice exists, use that?
        // Usually invoice->discount_amount is global discount. 
        // Here we are recalculating from LINES.
        // Assuming line_total is (Qty * UnitPrice) - LineDiscount + Tax.

        $subtotal = $grossTotal - $taxAmount; // Back-calculate Net Subtotal
        $total = $grossTotal - ($this->discount_amount ?? 0) + ($this->delivery_fee ?? 0);

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);

        // Calculate balance due based on total and paid amount
        $balanceDue = max(0, $total - $this->paid_amount);

        // Determine correct status based on payment
        $status = $this->status;

        if ($this->paid_amount > 0) {
            if ($balanceDue > 0) {
                // Determine if it was PENDING/DRAFT, switch to PARTIAL
                // If it was PAID but balance is > 0, switch to PARTIAL
                $status = SalesInvoiceStatus::PARTIAL;
            } else {
                // Balance is 0 or less -> PAID
                $status = SalesInvoiceStatus::PAID;
            }
        } elseif ($balanceDue <= 0 && $total > 0) {
            // Edge case: Paid=0, Total=0? No, Total>0. Balance<=0 means Total <= Paid(0)? Impossible if Total>0.
            // Unless Paid is negative? (Refund scenario).
            // If Total=0, handled below.
        } else if ($total == 0) {
            $status = SalesInvoiceStatus::PAID;
        }

        $this->update([
            'balance_due' => $balanceDue,
            'status' => $status,
        ]);
    }

    public function addPayment(float $amount): void
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $newBalance = $this->total - $newPaidAmount;

        $status = SalesInvoiceStatus::PARTIAL;
        if ($newBalance <= 0) {
            $status = SalesInvoiceStatus::PAID;
            $newBalance = 0;
        }

        $this->update([
            'paid_amount' => $newPaidAmount,
            'balance_due' => $newBalance,
            'status' => $status,
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->status->canReceivePayment() && $this->due_date->isPast();
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }
}
