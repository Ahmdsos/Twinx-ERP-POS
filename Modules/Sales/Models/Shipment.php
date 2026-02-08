<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Sales\Enums\ShipmentStatus;
use Modules\Core\Traits\HasDocumentNumber;
use App\Models\User;

/**
 * Shipment Model - تتبع الشحنات
 * 
 * Tracks the shipping/delivery status of delivery orders
 */
class Shipment extends Model
{
    use HasDocumentNumber;

    protected $fillable = [
        'shipment_number',
        'delivery_order_id',
        'courier_id',
        'tracking_number',
        'shipped_date',
        'expected_delivery_date',
        'delivered_date',
        'status',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'weight',
        'shipping_cost',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'shipped_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivered_date' => 'date',
        'status' => ShipmentStatus::class,
        'weight' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => ShipmentStatus::PENDING,
    ];

    // ========================================
    // Document Number Implementation
    // ========================================

    public function getDocumentPrefix(): string
    {
        return 'SHP';
    }

    public function getDocumentNumberField(): string
    {
        return 'shipment_number';
    }

    // ========================================
    // Relationships
    // ========================================

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ShipmentStatusHistory::class)->orderByDesc('created_at');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopePending($query)
    {
        return $query->where('status', ShipmentStatus::PENDING);
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', ShipmentStatus::IN_TRANSIT);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', ShipmentStatus::DELIVERED);
    }

    // ========================================
    // Business Methods
    // ========================================

    /**
     * Get tracking URL from courier
     */
    public function getTrackingUrl(): ?string
    {
        return $this->courier?->getTrackingUrl($this->tracking_number);
    }

    /**
     * Mark as shipped
     */
    public function markAsShipped(): bool
    {
        if ($this->status !== ShipmentStatus::PENDING) {
            return false;
        }

        $oldStatus = $this->status;
        $this->update([
            'status' => ShipmentStatus::IN_TRANSIT,
            'shipped_date' => now(),
        ]);

        $this->logStatusChange($oldStatus, ShipmentStatus::IN_TRANSIT);
        return true;
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered(): bool
    {
        if (!in_array($this->status, [ShipmentStatus::PENDING, ShipmentStatus::IN_TRANSIT])) {
            return false;
        }

        $oldStatus = $this->status;
        $this->update([
            'status' => ShipmentStatus::DELIVERED,
            'delivered_date' => now(),
        ]);

        $this->logStatusChange($oldStatus, ShipmentStatus::DELIVERED);

        // Update delivery order status via Service (H-08: Accounting Hook)
        if ($this->deliveryOrder) {
            app(\Modules\Sales\Services\SalesService::class)->completeDelivery($this->deliveryOrder);
        }

        return true;
    }

    /**
     * Log status change
     */
    protected function logStatusChange(ShipmentStatus $from, ShipmentStatus $to, ?string $notes = null): void
    {
        ShipmentStatusHistory::create([
            'shipment_id' => $this->id,
            'from_status' => $from,
            'to_status' => $to,
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }
}
