<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Inventory\Models\Product;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'website',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get products associated with this brand.
     * 
     * ⚠️ WARNING: This relationship requires Product.brand_id column.
     * Current Product model uses 'brand' as string column.
     * 
     * TODO: Create migration to add brand_id FK to products table,
     * then migrate existing 'brand' string values to proper FK references.
     * 
     * Alternative: Use scopeForBrand() on Product model with name matching.
     */
    public function products()
    {
        // Note: This will not work until brand_id is added to products table
        return $this->hasMany(Product::class, 'brand_id');
    }
}
