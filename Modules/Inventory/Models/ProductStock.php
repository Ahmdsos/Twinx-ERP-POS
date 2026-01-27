<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductStock Model - Current stock levels per warehouse
 */
class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stock';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'total_cost',
        'average_cost',
        'last_movement_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'average_cost' => 'decimal:4',
        'last_movement_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Reserve stock for an order
     */
    public function reserve(float $quantity): bool
    {
        if ($quantity > $this->available_quantity) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    /**
     * Release reserved stock
     */
    public function unreserve(float $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    /**
     * Update stock after movement
     */
    public function updateFromMovement(float $quantityChange, float $costChange): void
    {
        $this->quantity += $quantityChange;
        $this->total_cost += $costChange;

        // Recalculate average cost
        if ($this->quantity > 0) {
            $this->average_cost = $this->total_cost / $this->quantity;
        } else {
            $this->average_cost = 0;
        }

        $this->last_movement_at = now();
        $this->save();
    }

    /**
     * Get or create stock record for product/warehouse
     */
    public static function getOrCreate(int $productId, int $warehouseId): self
    {
        return static::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'reserved_quantity' => 0, 'total_cost' => 0, 'average_cost' => 0]
        );
    }
}
