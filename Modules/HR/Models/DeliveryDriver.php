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

    protected $casts = [
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
}
