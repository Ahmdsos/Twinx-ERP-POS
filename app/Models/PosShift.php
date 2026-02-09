<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PosShift Model
 * Represents a cashier shift in the POS system
 */
class PosShift extends Model
{
    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_difference',
        'total_sales',
        'total_amount',
        'total_cash',
        'total_card',
        'total_credit',
        'status',
        'closing_notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_card' => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(\Modules\Sales\Models\SalesInvoice::class, 'pos_shift_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(\Modules\Finance\Models\Expense::class, 'pos_shift_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(\Modules\Sales\Models\SalesReturn::class, 'shift_id');
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Helpers
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public static function getActiveShift($userId = null): ?self
    {
        $userId = $userId ?? auth()->id();
        return static::open()->forUser($userId)->first();
    }

    public static function openNewShift(float $openingCash = 0, $userId = null): self
    {
        $userId = $userId ?? auth()->id();

        // Close any existing open shifts for this user
        $existingShifts = static::open()->forUser($userId)->get();
        /** @var self $shift */
        foreach ($existingShifts as $shift) {
            $shift->close($shift->opening_cash + $shift->total_cash, 'System Auto-Close'); // Force close
        }

        return static::create([
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'status' => 'open',
        ]);
    }

    public function getCurrentBalance(): float
    {
        $totalExpenses = $this->expenses()->sum('amount');
        $totalReturns = $this->returns()->sum('total_amount');

        return ($this->opening_cash + $this->total_cash) - $totalExpenses - $totalReturns;
    }

    public function close(float $closingCash, ?string $notes = null): self
    {
        $expectedCash = $this->getCurrentBalance();

        $this->update([
            'closed_at' => now(),
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $closingCash - $expectedCash,
            'status' => 'closed',
            'closing_notes' => $notes,
        ]);

        return $this;
    }

    public function incrementTransaction(): void
    {
        $this->increment('total_sales'); // Transaction Count
    }

    public function incrementSales(float $amount, string $paymentMethod = 'cash'): void
    {
        $this->increment('total_amount', $amount); // Value

        if ($paymentMethod === 'cash') {
            $this->increment('total_cash', $amount);
        } elseif ($paymentMethod === 'card') {
            $this->increment('total_card', $amount);
        } elseif ($paymentMethod === 'credit') {
            $this->increment('total_credit', $amount);
        }
    }
}
