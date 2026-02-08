<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Sales\Enums\SalesOrderStatus;
use Modules\Inventory\Models\Warehouse;

/**
 * SalesOrder Model - SO Header
 */
class SalesOrder extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'so_number',
        'customer_id',
        'order_date',
        'expected_date',
        'warehouse_id',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'currency',
        'exchange_rate',
        'reference',
        'notes',
        'customer_notes',
        'shipping_address',
        'shipping_method',
        'quotation_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'status' => SalesOrderStatus::class,
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'quotation_id' => 'integer',
    ];

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        return \App\Models\Setting::getValue('sales_order_prefix', 'SO');
    }

    public function getDocumentNumberField(): string
    {
        return 'so_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SalesOrderLine::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeStatus($query, SalesOrderStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAwaitingDelivery($query)
    {
        return $query->whereIn('status', [
            SalesOrderStatus::CONFIRMED,
            SalesOrderStatus::PROCESSING,
            SalesOrderStatus::PARTIAL,
        ]);
    }

    // ========================================
    // Business Methods
    // ========================================

    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function canDeliver(): bool
    {
        return $this->status->canDeliver();
    }

    public function canInvoice(): bool
    {
        return $this->status->canInvoice();
    }

    public function canCancel(): bool
    {
        return $this->status->canCancel();
    }

    public function confirm(): bool
    {
        if ($this->status !== SalesOrderStatus::DRAFT) {
            return false;
        }
        $this->update(['status' => SalesOrderStatus::CONFIRMED]);
        return true;
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->lines()->sum('line_total');
        $taxAmount = $this->lines()->sum('tax_amount');
        $discountAmount = $this->lines()->sum('discount_amount');

        $this->update([
            'subtotal' => $subtotal + $discountAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ]);
    }

    public function getDeliveredPercentage(): float
    {
        $totalQty = $this->lines()->sum('quantity');
        $deliveredQty = $this->lines()->sum('delivered_quantity');
        return $totalQty > 0 ? round(($deliveredQty / $totalQty) * 100, 2) : 0;
    }

    public function updateDeliveryStatus(): void
    {
        $percentage = $this->getDeliveredPercentage();

        if ($percentage >= 100) {
            $this->update(['status' => SalesOrderStatus::DELIVERED]);
        } elseif ($percentage > 0) {
            $this->update(['status' => SalesOrderStatus::PARTIAL]);
        }
    }
}
