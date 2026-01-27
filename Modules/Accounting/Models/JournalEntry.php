<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounting\Enums\JournalStatus;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;

/**
 * JournalEntry Model - Transaction Header
 * 
 * Represents a complete accounting transaction with multiple lines.
 * Must always be balanced (total debits = total credits).
 * 
 * @property int $id
 * @property string $entry_number
 * @property \Carbon\Carbon $entry_date
 * @property int|null $fiscal_year_id
 * @property string|null $reference
 * @property string|null $description
 * @property JournalStatus $status
 * @property float $total_debit
 * @property float $total_credit
 */
class JournalEntry extends Model
{
    use HasFactory, SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'fiscal_year_id',
        'reference',
        'source_type',
        'source_id',
        'description',
        'status',
        'total_debit',
        'total_credit',
        'posted_at',
        'posted_by',
        'reversed_by_entry_id',
        'reversed_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'status' => JournalStatus::class,
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    /**
     * Get the document prefix for auto-numbering
     */
    public function getDocumentPrefix(): string
    {
        return config('erp.numbering.journal_entry.prefix', 'JE');
    }

    /**
     * Get the field name for document number
     */
    public function getDocumentNumberField(): string
    {
        return 'entry_number';
    }

    /**
     * Get all lines for this entry
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    /**
     * Get the fiscal year
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Get the user who posted this entry
     */
    public function postedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }

    /**
     * Get the reversal entry (if reversed)
     */
    public function reversalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_by_entry_id');
    }

    /**
     * Get the source document (polymorphic)
     */
    public function source()
    {
        if ($this->source_type && $this->source_id) {
            return $this->morphTo('source', 'source_type', 'source_id');
        }
        return null;
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, JournalStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get posted entries only
     */
    public function scopePosted($query)
    {
        return $query->where('status', JournalStatus::POSTED);
    }

    /**
     * Scope to get draft entries only
     */
    public function scopeDraft($query)
    {
        return $query->where('status', JournalStatus::DRAFT);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('entry_date', [$startDate, $endDate]);
    }

    /**
     * Check if entry is balanced
     */
    public function isBalanced(): bool
    {
        $precision = pow(10, -config('erp.currency.decimal_places', 2));
        return abs($this->total_debit - $this->total_credit) < $precision;
    }

    /**
     * Check if entry can be edited
     */
    public function isEditable(): bool
    {
        return $this->status === JournalStatus::DRAFT;
    }

    /**
     * Check if entry can be posted
     */
    public function canBePosted(): bool
    {
        return $this->status === JournalStatus::DRAFT
            && $this->isBalanced()
            && $this->lines()->count() >= 2;
    }

    /**
     * Check if entry can be reversed
     */
    public function canBeReversed(): bool
    {
        return $this->status === JournalStatus::POSTED
            && is_null($this->reversed_by_entry_id);
    }

    /**
     * Recalculate totals from lines
     */
    public function recalculateTotals(): void
    {
        $this->total_debit = $this->lines()->sum('debit');
        $this->total_credit = $this->lines()->sum('credit');
        $this->save();
    }
}
