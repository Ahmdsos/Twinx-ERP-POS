<?php

namespace Modules\HR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryDriver extends Model
{
    use HasFactory;

    protected $table = 'hr_delivery_drivers';

    protected $fillable = [
        'employee_id',
        'license_number',
        'license_expiry',
        'vehicle_info',
        'status',
        'rating',
        'total_deliveries',
    ];

    protected $guarded = [];

    protected $casts = [
        'status' => \Modules\HR\Enums\DeliveryDriverStatus::class,
        'license_expiry' => 'date',
        'rating' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    /**
     * Get the delivery orders assigned to this driver.
     */
    public function shipments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Sales\Models\DeliveryOrder::class, 'driver_id');
    }

    /**
     * Get currently active shipments (Shipped or Ready)
     */
    public function activeShipments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->shipments()->whereIn('status', [
            \Modules\Sales\Enums\DeliveryStatus::SHIPPED,
            \Modules\Sales\Enums\DeliveryStatus::READY
        ]);
    }

    /**
     * Calculate Success Rate percentage
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->shipments()->whereIn('status', [
            \Modules\Sales\Enums\DeliveryStatus::DELIVERED,
            \Modules\Sales\Enums\DeliveryStatus::RETURNED
        ])->count();

        if ($total === 0)
            return 0;

        $success = $this->shipments()->where('status', \Modules\Sales\Enums\DeliveryStatus::DELIVERED)->count();

        return round(($success / $total) * 100, 1);
    }

    /**
     * Check if driver is currently in the field
     */
    public function getIsInFieldAttribute(): bool
    {
        return $this->activeShipments()->exists();
    }

    public function occupy(): void
    {
        $this->update(['status' => 'on_delivery']);
    }

    public function release(): void
    {
        $this->update(['status' => 'available']);
    }
}
