<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Purchasing\Enums\PurchaseInvoiceStatus;
use Modules\Accounting\Models\JournalEntry;

/**
 * PurchaseInvoice Model - Supplier Bills
 */
class PurchaseInvoice extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'invoice_number',
        'supplier_invoice_number',
        'supplier_id',
        'grn_id',
        'purchase_order_id',
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
        'journal_entry_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'status' => PurchaseInvoiceStatus::class,
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
        $config = config('erp.numbering.purchase_invoice', ['prefix' => 'PI']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'invoice_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            PurchaseInvoiceStatus::PENDING,
            PurchaseInvoiceStatus::PARTIAL,
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

        $status = PurchaseInvoiceStatus::PARTIAL;
        if ($newBalance <= 0) {
            $status = PurchaseInvoiceStatus::PAID;
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
        return $this->status->canPay() && $this->due_date->isPast();
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }
}
