<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Exchange Rate History Model
 */
class ExchangeRate extends Model
{
    protected $fillable = [
        'currency_id',
        'rate',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get rate for a specific date
     */
    public static function getRateForDate(int $currencyId, string $date): ?float
    {
        return static::where('currency_id', $currencyId)
            ->where('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->value('rate');
    }
}
