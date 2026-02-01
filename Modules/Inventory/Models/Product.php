<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Enums\ProductType;
use Modules\Core\Traits\HasAuditTrail;

/**
 * Product Model - Product Master
 */
class Product extends Model
{
    use HasFactory, SoftDeletes, HasAuditTrail;

    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'type',
        'category_id',
        'brand_id',
        'unit_id',
        'purchase_unit_id',
        'cost_price',
        'selling_price',
        'min_selling_price',
        'tax_rate',
        'is_tax_inclusive',
        'reorder_level',
        'reorder_quantity',
        'min_stock',
        'max_stock',
        'sales_account_id',
        'purchase_account_id',
        'inventory_account_id',
        'is_active',
        'is_sellable',
        'is_purchasable',
        // New extended fields
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dimension_unit',
        // 'brand', // Removed to avoid conflict with relationship
        'manufacturer',
        'manufacturer_part_number',
        'warranty_months',
        'warranty_type',
        'expiry_date',
        'shelf_life_days',
        'track_batches',
        'track_serials',
        'country_of_origin',
        'hs_code',
        'lead_time_days',
        'is_returnable',
        'color',
        'size',
        'price_distributor',
        'price_wholesale',
        'price_half_wholesale',
        'price_quarter_wholesale',
        'price_special',
        'tags',
        'seo_title',
        'seo_description',
    ];

    protected $appends = ['stock_qty'];

    protected $casts = [
        'type' => ProductType::class,
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'price_distributor' => 'decimal:2',
        'price_wholesale' => 'decimal:2',
        'price_half_wholesale' => 'decimal:2',
        'price_quarter_wholesale' => 'decimal:2',
        'price_special' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'is_active' => 'boolean',
        'is_sellable' => 'boolean',
        'is_purchasable' => 'boolean',
        // New field casts
        'weight' => 'decimal:4',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'warranty_months' => 'integer',
        'expiry_date' => 'date',
        'shelf_life_days' => 'integer',
        'track_batches' => 'boolean',
        'track_serials' => 'boolean',
        'lead_time_days' => 'integer',
        'is_returnable' => 'boolean',
        'tags' => 'array',
    ];

    // =====================
    // Relationships
    // =====================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(\Modules\Accounting\Models\Account::class, 'sales_account_id');
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(\Modules\Accounting\Models\Account::class, 'purchase_account_id');
    }

    public function inventoryAccount(): BelongsTo
    {
        return $this->belongsTo(\Modules\Accounting\Models\Account::class, 'inventory_account_id');
    }

    public function stock(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Alias for stock() - used by StockReportService
     */
    public function stocks(): HasMany
    {
        return $this->stock();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->ordered();
    }

    /**
     * Get primary image
     */
    public function getPrimaryImageAttribute(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->primaryImage?->url;
    }

    // =====================
    // Scopes
    // =====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSellable($query)
    {
        return $query->active()->where('is_sellable', true);
    }

    public function scopePurchasable($query)
    {
        return $query->active()->where('is_purchasable', true);
    }

    public function scopeGoods($query)
    {
        return $query->where('type', ProductType::GOODS);
    }

    public function scopeServices($query)
    {
        return $query->where('type', ProductType::SERVICE);
    }

    // =====================
    // Helpers
    // =====================

    /**
     * Does this product track inventory?
     */
    public function tracksInventory(): bool
    {
        return $this->type->tracksInventory();
    }

    /**
     * Get total stock across all warehouses
     */
    public function getTotalStockAttribute(): float
    {
        return $this->stock()->sum('quantity');
    }

    /**
     * Get total stock as method (for controller compatibility)
     */
    public function getTotalStock(): float
    {
        return $this->total_stock;
    }

    /**
     * Get available stock across all warehouses
     */
    public function getAvailableStockAttribute(): float
    {
        return $this->stock()->sum('available_quantity');
    }

    /**
     * Get stock in a specific warehouse
     */
    public function getStockInWarehouse(int $warehouseId): ?ProductStock
    {
        return $this->stock()->where('warehouse_id', $warehouseId)->first();
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->reorder_level;
    }

    /**
     * Get price based on customer type
     */
    public function getPriceForCustomerType(?string $customerType): float
    {
        return match ($customerType) {
            'distributor' => $this->price_distributor > 0 ? $this->price_distributor : $this->selling_price,
            'wholesale' => $this->price_wholesale > 0 ? $this->price_wholesale : $this->selling_price,
            'half_wholesale' => $this->price_half_wholesale > 0 ? $this->price_half_wholesale : $this->selling_price,
            'quarter_wholesale' => $this->price_quarter_wholesale > 0 ? $this->price_quarter_wholesale : $this->selling_price,
            'technician', 'employee', 'vip' => $this->price_special > 0 ? $this->price_special : $this->selling_price,
            default => $this->selling_price,
        };
    }

    /**
     * Calculate selling price with tax
     */
    public function getPriceWithTax(): float
    {
        if ($this->is_tax_inclusive) {
            return $this->selling_price;
        }
        return $this->selling_price * (1 + $this->tax_rate / 100);
    }

    /**
     * Calculate selling price without tax
     */
    public function getPriceWithoutTax(): float
    {
        if (!$this->is_tax_inclusive) {
            return $this->selling_price;
        }
        return $this->selling_price / (1 + $this->tax_rate / 100);
    }


    /**
     * Accessor for stock_qty used in POS
     * 
     * DERIVED VALUE - Source of Truth: product_stock.quantity
     * This is NOT a stored column. It sums from the product_stock table.
     */
    public function getStockQtyAttribute(): float
    {
        return $this->total_stock;
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }
}
