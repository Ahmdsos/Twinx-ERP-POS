<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Accounting\Models\Account;
use App\Models\User;

/**
 * Customer Model - Customer Master
 */
class Customer extends Model
{
    use SoftDeletes, HasAuditTrail;

    protected $fillable = [
        'code',
        'name',
        'type',
        'email',
        'phone',
        'mobile',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_postal',
        'shipping_address',
        'shipping_city',
        'shipping_country',
        'shipping_postal',
        'tax_number',
        'payment_terms',
        'credit_limit',
        'account_id',
        'sales_rep_id',
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

    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
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

        static::creating(function ($customer) {
            if (empty($customer->code)) {
                $customer->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $last = self::withTrashed()->orderByDesc('id')->first();
        $nextNumber = $last ? ((int) substr($last->code, 4)) + 1 : 1;
        return 'CUS-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // ========================================
    // Business Methods
    // ========================================

    public function getTotalSales(): float
    {
        return $this->salesInvoices()
            ->where('status', '!=', 'cancelled')
            ->sum('total');
    }

    public function getOutstandingBalance(): float
    {
        return $this->salesInvoices()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');
    }

    public function getAvailableCredit(): float
    {
        return max(0, $this->credit_limit - $this->getOutstandingBalance());
    }

    public function getShippingAddressFormatted(): string
    {
        if ($this->shipping_address) {
            return implode(', ', array_filter([
                $this->shipping_address,
                $this->shipping_city,
                $this->shipping_country,
            ]));
        }
        return implode(', ', array_filter([
            $this->billing_address,
            $this->billing_city,
            $this->billing_country,
        ]));
    }
}
