<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use App\Models\User;

class Expense extends Model
{
    use SoftDeletes, HasDocumentNumber;

    protected $fillable = [
        'reference_number',
        'expense_date',
        'category_id',
        'payment_account_id',
        'amount',
        'tax_amount',
        'total_amount',
        'payee',
        'notes',
        'attachment',
        'status',
        'journal_entry_id',
        'created_by',
        'approved_by',
        'pos_shift_id',
        'user_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'pos_shift_id' => 'integer',
    ];

    public function getDocumentPrefix(): string
    {
        return 'EXP-';
    }

    public function getDocumentNumberField(): string
    {
        return 'reference_number';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
