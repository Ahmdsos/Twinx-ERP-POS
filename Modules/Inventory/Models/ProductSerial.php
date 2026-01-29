<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductSerial Model
 * For tracking individual serial numbers with warranty
 */
class ProductSerial extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'serial_number',
        'status',
        'unit_cost',
        'warranty_start',
        'warranty_end',
        'purchase_invoice_id',
        'sales_invoice_id',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    // Status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_SOLD = 'sold';
    const STATUS_RESERVED = 'reserved';
    const STATUS_RETURNED = 'returned';
    const STATUS_DAMAGED = 'damaged';

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeSold($query)
    {
        return $query->where('status', self::STATUS_SOLD);
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeUnderWarranty($query)
    {
        return $query->whereNotNull('warranty_end')
            ->where('warranty_end', '>=', now());
    }

    // Accessors
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function getIsUnderWarrantyAttribute(): bool
    {
        return $this->warranty_end && $this->warranty_end->isFuture();
    }

    public function getWarrantyDaysRemainingAttribute(): ?int
    {
        if (!$this->warranty_end)
            return null;
        if ($this->warranty_end->isPast())
            return 0;
        return now()->diffInDays($this->warranty_end);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'متاح',
            self::STATUS_SOLD => 'مباع',
            self::STATUS_RESERVED => 'محجوز',
            self::STATUS_RETURNED => 'مرتجع',
            self::STATUS_DAMAGED => 'تالف',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_SOLD => 'primary',
            self::STATUS_RESERVED => 'warning',
            self::STATUS_RETURNED => 'info',
            self::STATUS_DAMAGED => 'danger',
            default => 'secondary',
        };
    }

    // Methods
    public function markAsSold(int $salesInvoiceId): void
    {
        $this->update([
            'status' => self::STATUS_SOLD,
            'sales_invoice_id' => $salesInvoiceId,
        ]);
    }

    public function markAsReturned(): void
    {
        $this->update([
            'status' => self::STATUS_RETURNED,
        ]);
    }
}
