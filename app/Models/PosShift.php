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
        static::open()->forUser($userId)->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return static::create([
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'status' => 'open',
        ]);
    }

    public function close(float $closingCash, ?string $notes = null): self
    {
        $this->update([
            'closed_at' => now(),
            'closing_cash' => $closingCash,
            'expected_cash' => $this->opening_cash + $this->total_cash,
            'cash_difference' => $closingCash - ($this->opening_cash + $this->total_cash),
            'status' => 'closed',
            'closing_notes' => $notes,
        ]);

        return $this;
    }

    public function incrementSales(float $amount, string $paymentMethod = 'cash'): void
    {
        $this->increment('total_sales');
        $this->increment('total_amount', $amount);

        if ($paymentMethod === 'cash') {
            $this->increment('total_cash', $amount);
        } else {
            $this->increment('total_card', $amount);
        }
    }
}
