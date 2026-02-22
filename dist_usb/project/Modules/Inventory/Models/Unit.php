<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Unit Model - Unit of Measure
 */
class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'is_base',
        'base_unit_id',
        'conversion_factor',
        'is_active',
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'conversion_factor' => 'decimal:6',
    ];

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function derivedUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Convert quantity from this unit to base unit
     */
    public function toBase(float $quantity): float
    {
        return $quantity * $this->conversion_factor;
    }

    /**
     * Convert quantity from base unit to this unit
     */
    public function fromBase(float $quantity): float
    {
        return $quantity / $this->conversion_factor;
    }
}
