<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use App\Models\User;

/**
 * SupplierPayment Model - Payments to suppliers
 */
class SupplierPayment extends Model
{
    use SoftDeletes, HasDocumentNumber;

    protected $fillable = [
        'payment_number',
        'supplier_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_account_id',
        'reference',
        'notes',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        $config = config('erp.numbering.payment_voucher', ['prefix' => 'PV']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'payment_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(SupplierPaymentAllocation::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(
            PurchaseInvoice::class,
            'supplier_payment_allocations',
            'supplier_payment_id',
            'purchase_invoice_id'
        )->withPivot('amount')->withTimestamps();
    }

    // ========================================
    // Business Methods
    // ========================================

    public function getAllocatedAmount(): float
    {
        return $this->allocations()->sum('amount');
    }

    public function getUnallocatedAmount(): float
    {
        return max(0, $this->amount - $this->getAllocatedAmount());
    }

    public function allocateToInvoice(PurchaseInvoice $invoice, float $amount): void
    {
        $allocation = SupplierPaymentAllocation::create([
            'supplier_payment_id' => $this->id,
            'purchase_invoice_id' => $invoice->id,
            'amount' => $amount,
        ]);

        $invoice->addPayment($amount);
    }
}
