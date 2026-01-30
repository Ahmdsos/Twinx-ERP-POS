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
        'pos_shift_id',
        'payment_method',
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

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        $config = config('erp.numbering.sales_invoice', ['prefix' => 'INV']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'invoice_number';
    }

    // ========================================
    // Relationships
    // ========================================

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

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PosShift::class, 'pos_shift_id');
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
        $subtotal = $this->lines()->sum('line_total');
        $taxAmount = $this->lines()->sum('tax_amount');
        $discountAmount = $this->lines()->sum('discount_amount');

        $total = $subtotal + $taxAmount - $discountAmount;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'balance_due' => $total - $this->paid_amount,
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
