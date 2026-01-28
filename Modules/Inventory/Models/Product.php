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
    ];

    protected $casts = [
        'type' => ProductType::class,
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_selling_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'is_active' => 'boolean',
        'is_sellable' => 'boolean',
        'is_purchasable' => 'boolean',
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
}
