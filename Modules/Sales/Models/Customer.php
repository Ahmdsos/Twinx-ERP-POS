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
        'credit_grace_days',
        'account_id',
        'sales_rep_id',
        'contact_person',
        'notes',
        'is_active',
        'is_blocked',
        'block_reason',
        'blocked_at',
        'blocked_by',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'credit_limit' => 'decimal:2',
        'credit_grace_days' => 'integer',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
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

    // ========================================
    // Credit Control Methods
    // ========================================

    /**
     * Check if customer can place new order
     */
    public function canPlaceOrder(float $orderAmount = 0): bool
    {
        // Blocked customers cannot place orders
        if ($this->is_blocked) {
            return false;
        }

        // If no credit limit set (0 = unlimited)
        if ($this->credit_limit <= 0) {
            return true;
        }

        // Check if order would exceed credit limit
        return $this->getAvailableCredit() >= $orderAmount;
    }

    /**
     * Get reason why customer cannot order
     */
    public function getOrderBlockReason(float $orderAmount = 0): ?string
    {
        if ($this->is_blocked) {
            return $this->block_reason ?? 'العميل محظور من إنشاء طلبات جديدة';
        }

        if ($this->credit_limit > 0 && $this->getAvailableCredit() < $orderAmount) {
            return sprintf(
                'تجاوز حد الائتمان. الحد: %s، المستخدم: %s، المتاح: %s',
                number_format($this->credit_limit, 2),
                number_format($this->getOutstandingBalance(), 2),
                number_format($this->getAvailableCredit(), 2)
            );
        }

        return null;
    }

    /**
     * Block customer
     */
    public function block(string $reason, ?int $userId = null): bool
    {
        return $this->update([
            'is_blocked' => true,
            'block_reason' => $reason,
            'blocked_at' => now(),
            'blocked_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Unblock customer
     */
    public function unblock(): bool
    {
        return $this->update([
            'is_blocked' => false,
            'block_reason' => null,
            'blocked_at' => null,
            'blocked_by' => null,
        ]);
    }

    /**
     * Check if customer has overdue invoices
     */
    public function hasOverdueInvoices(): bool
    {
        return $this->salesInvoices()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->exists();
    }

    /**
     * Get overdue invoice count
     */
    public function getOverdueInvoiceCount(): int
    {
        return $this->salesInvoices()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->count();
    }

    /**
     * Get total overdue amount
     */
    public function getOverdueAmount(): float
    {
        return $this->salesInvoices()
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now()->toDateString())
            ->sum('balance_due');
    }

    /**
     * Auto-block customer if has overdue invoices (called by scheduler/job)
     */
    public function checkAndBlockIfOverdue(): bool
    {
        if ($this->is_blocked) {
            return false; // Already blocked
        }

        $overdueCount = $this->getOverdueInvoiceCount();

        // Block if has overdue invoices beyond grace period
        if ($overdueCount > 0) {
            $oldestOverdue = $this->salesInvoices()
                ->whereIn('status', ['pending', 'partial'])
                ->where('due_date', '<', now()->toDateString())
                ->orderBy('due_date')
                ->first();

            if ($oldestOverdue) {
                $daysOverdue = now()->diffInDays($oldestOverdue->due_date);

                // Block if overdue beyond grace period
                if ($daysOverdue > $this->credit_grace_days) {
                    $this->block("فواتير متأخرة ({$overdueCount}) - أقدم تأخير: {$daysOverdue} يوم");
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Relationship: Blocker user
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}
