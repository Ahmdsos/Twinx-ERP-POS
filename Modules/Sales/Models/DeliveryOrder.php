<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasDocumentNumber;
use Modules\Core\Traits\HasAuditTrail;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Models\JournalEntry;
use App\Models\User;

/**
 * DeliveryOrder Model - DO for shipping goods
 */
class DeliveryOrder extends Model
{
    use SoftDeletes, HasDocumentNumber, HasAuditTrail;

    protected $fillable = [
        'do_number',
        'sales_order_id',
        'customer_id',
        'warehouse_id',
        'delivery_date',
        'shipped_date',
        'status',
        'shipping_address',
        'shipping_method',
        'tracking_number',
        'driver_name',
        'driver_id',
        'vehicle_number',
        'notes',
        'journal_entry_id',
        'delivered_by',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'shipped_date' => 'date',
        'status' => DeliveryStatus::class,
    ];

    // Implement HasDocumentNumber trait methods
    public function getDocumentPrefix(): string
    {
        $config = config('erp.numbering.delivery_order', ['prefix' => 'DO']);
        return $config['prefix'];
    }

    public function getDocumentNumberField(): string
    {
        return 'do_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function driver(): BelongsTo
    {
        return $this->belongsTo(\Modules\HR\Models\DeliveryDriver::class, 'driver_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DeliveryOrderLine::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeReady($query)
    {
        return $query->where('status', DeliveryStatus::READY);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', DeliveryStatus::DELIVERED);
    }

    // ========================================
    // Business Methods
    // ========================================

    public function canEdit(): bool
    {
        return $this->status->canEdit();
    }

    public function getTotalCost(): float
    {
        return $this->lines()->sum('line_cost');
    }

    public function ship(): bool
    {
        if ($this->status !== DeliveryStatus::READY) {
            return false;
        }
        $this->update([
            'status' => DeliveryStatus::SHIPPED,
            'shipped_date' => now(),
        ]);
        return true;
    }

    public function complete(): bool
    {
        if (!in_array($this->status, [DeliveryStatus::READY, DeliveryStatus::SHIPPED])) {
            return false;
        }
        $this->update([
            'status' => DeliveryStatus::DELIVERED,
            'delivered_by' => auth()->id(),
        ]);
        return true;
    }
}
