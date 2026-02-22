<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FiscalYear Model - Accounting Period
 * 
 * Defines the fiscal year boundaries for financial reporting.
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $is_active
 * @property bool $is_closed
 */
class FiscalYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user who closed this fiscal year
     */
    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }

    /**
     * Scope to get the current active fiscal year
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get open (not closed) fiscal years
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Check if a date falls within this fiscal year
     */
    public function containsDate($date): bool
    {
        $date = \Carbon\Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }

    /**
     * Get the current active fiscal year
     */
    public static function current(): ?self
    {
        return static::active()->open()->first();
    }

    /**
     * Get fiscal year for a specific date
     */
    public static function forDate($date): ?self
    {
        $date = \Carbon\Carbon::parse($date);
        return static::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }
}
