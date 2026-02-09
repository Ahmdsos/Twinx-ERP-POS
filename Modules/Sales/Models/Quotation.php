<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Sales\Enums\QuotationStatus;
use App\Models\User;

/**
 * Quotation Model - Sales Quotations/Estimates
 * 
 * Workflow: Draft → Send to Customer → Accept/Reject → Convert to Sales Order
 */
class Quotation extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'quotation_number',
        'customer_id',
        'target_customer_type',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'currency',
        'exchange_rate',
        'notes',
        'terms',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'status' => QuotationStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => QuotationStatus::DRAFT,
        'currency' => 'EGP',
        'exchange_rate' => 1,
        'discount_amount' => 0,
        'tax_amount' => 0,
    ];

    // ========================================
    // Document Number Implementation
    // ========================================

    public function getDocumentPrefix(): string
    {
        return \App\Models\Setting::getValue('quotation_prefix', 'QT');
    }

    public function getDocumentNumberField(): string
    {
        return 'quotation_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'quotation_customer');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuotationLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function salesOrder(): HasOne
    {
        return $this->hasOne(SalesOrder::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeDraft($query)
    {
        return $query->where('status', QuotationStatus::DRAFT);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [QuotationStatus::DRAFT, QuotationStatus::SENT]);
    }

    public function scopeValid($query)
    {
        return $query->where('valid_until', '>=', now()->toDateString());
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now()->toDateString())
            ->where('status', '!=', QuotationStatus::CONVERTED);
    }

    // ========================================
    // Business Methods
    // ========================================

    /**
     * Calculate totals from lines
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->lines->sum('line_total');
        $taxAmount = $this->lines->sum(fn($line) => $line->line_total * ($line->tax_percent / 100));

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount - $this->discount_amount,
        ]);
    }

    /**
     * Mark quotation as sent
     */
    public function markAsSent(): bool
    {
        if ($this->status !== QuotationStatus::DRAFT) {
            return false;
        }

        return $this->update(['status' => QuotationStatus::SENT]);
    }

    /**
     * Mark quotation as accepted
     */
    public function accept(?int $userId = null): bool
    {
        if (!in_array($this->status, [QuotationStatus::DRAFT, QuotationStatus::SENT])) {
            return false;
        }

        return $this->update([
            'status' => QuotationStatus::ACCEPTED,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Mark quotation as rejected
     */
    public function reject(): bool
    {
        if (!in_array($this->status, [QuotationStatus::DRAFT, QuotationStatus::SENT])) {
            return false;
        }

        return $this->update(['status' => QuotationStatus::REJECTED]);
    }

    /**
     * Check if quotation is expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->lt(now()->startOfDay());
    }

    /**
     * Check if quotation can be converted to sales order
     */
    public function canConvert(): bool
    {
        return $this->status === QuotationStatus::ACCEPTED && !$this->isExpired();
    }

    /**
     * Mark as converted (when SO is created)
     */
    public function markAsConverted(): bool
    {
        return $this->update(['status' => QuotationStatus::CONVERTED]);
    }

    /**
     * Get label for target customer type
     */
    public function getTargetCustomerTypeLabelAttribute(): string
    {
        if (empty($this->target_customer_type)) {
            return '-';
        }

        return \Modules\Sales\Enums\CustomerType::tryFrom($this->target_customer_type)?->label() ?? ucfirst($this->target_customer_type);
    }
}
