<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * JournalEntryLine Model - Individual debit/credit entry
 * 
 * Each line affects one account with either a debit or credit amount.
 * A line cannot have both debit and credit values simultaneously.
 * 
 * @property int $id
 * @property int $journal_entry_id
 * @property int $account_id
 * @property float $debit
 * @property float $credit
 * @property string|null $description
 */
class JournalEntryLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
        'cost_center',
        'subledger_type',
        'subledger_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /**
     * Get the parent journal entry
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * Get the account for this line
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the subledger entity (Customer, Supplier, etc.)
     */
    public function subledger()
    {
        if ($this->subledger_type && $this->subledger_id) {
            return $this->morphTo('subledger', 'subledger_type', 'subledger_id');
        }
        return null;
    }

    /**
     * Check if this is a debit line
     */
    public function isDebit(): bool
    {
        return $this->debit > 0;
    }

    /**
     * Check if this is a credit line
     */
    public function isCredit(): bool
    {
        return $this->credit > 0;
    }

    /**
     * Get the net amount (positive for debit, negative for credit)
     */
    public function getNetAmountAttribute(): float
    {
        return $this->debit - $this->credit;
    }

    /**
     * Get the absolute amount
     */
    public function getAmountAttribute(): float
    {
        return max($this->debit, $this->credit);
    }

    /**
     * Scope to filter debit lines only
     */
    public function scopeDebits($query)
    {
        return $query->where('debit', '>', 0);
    }

    /**
     * Scope to filter credit lines only
     */
    public function scopeCredits($query)
    {
        return $query->where('credit', '>', 0);
    }

    /**
     * Scope to filter by account
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Recalculate parent totals when line is saved or deleted
        static::saved(function ($line) {
            $line->journalEntry->recalculateTotals();
        });

        static::deleted(function ($line) {
            $line->journalEntry->recalculateTotals();
        });
    }
}
