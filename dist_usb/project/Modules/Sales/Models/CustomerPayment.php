<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use App\Models\User;

/**
 * CustomerPayment Model - Receipts from customers
 */
class CustomerPayment extends Model
{
    use SoftDeletes, HasDocumentNumber;

    protected $fillable = [
        'receipt_number',
        'customer_id',
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
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        $config = config('erp.numbering.payment_receipt', ['prefix' => 'RV']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'receipt_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(CustomerPaymentAllocation::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(
            SalesInvoice::class,
            'customer_payment_allocations',
            'customer_payment_id',
            'sales_invoice_id'
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

    public function allocateToInvoice(SalesInvoice $invoice, float $amount): void
    {
        CustomerPaymentAllocation::create([
            'customer_payment_id' => $this->id,
            'sales_invoice_id' => $invoice->id,
            'amount' => $amount,
        ]);

        $invoice->addPayment($amount);
    }
}
