<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Currency Model for Multi-Currency Support
 */
class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_active',
        'decimal_places',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active currencies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the default currency
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Convert amount from this currency to base currency
     */
    public function toBase(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * Convert amount from base currency to this currency
     */
    public function fromBase(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * Format amount with currency symbol
     */
    public function format(float $amount): string
    {
        return $this->symbol . ' ' . number_format($amount, $this->decimal_places);
    }

    /**
     * Exchange rate history
     */
    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class);
    }
}
