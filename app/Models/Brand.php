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

    public function products()
    {
        return $this->hasMany(Product::class); // Assuming Product model exists
        // Note: Product model needs 'brand_id' or similar if we link formally, 
        // currently Product uses 'brand' string column in existing schema.
        // We might want to Refactor Product to use brand_id later, 
        // but for now we stick to the plan: Create Brand management first.
    }
}
