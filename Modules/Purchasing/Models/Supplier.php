<?php

namespace Modules\Purchasing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Accounting\Models\Account;

/**
 * Supplier Model - Vendor/Supplier Master
 */
class Supplier extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'mobile',
        'fax',
        'address',
        'city',
        'country',
        'postal_code',
        'tax_number',
        'commercial_register',
        'payment_terms',
        'credit_limit',
        'account_id',
        'contact_person',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseInvoices(): HasMany
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ========================================
    // Auto-generate code
    // ========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->code)) {
                $supplier->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        // Use lockForUpdate() to prevent race condition during concurrent supplier creation
        $lastSupplier = self::withTrashed()
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $nextNumber = $lastSupplier ? ((int) substr($lastSupplier->code, 4)) + 1 : 1;

        return 'SUP-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ========================================
    // Business Methods
    // ========================================

    public function getTotalPurchases(): float
    {
        return $this->purchaseInvoices()
            ->where('status', '!=', 'cancelled')
            ->sum('total');
    }

    public function getOutstandingBalance(): float
    {
        return $this->purchaseInvoices()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');
    }
}
