<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Sales\Enums\ShipmentStatus;
use App\Models\User;

/**
 * ShipmentStatusHistory Model - سجل تتبع حالة الشحنة
 */
class ShipmentStatusHistory extends Model
{
    protected $table = 'shipment_status_history';

    protected $fillable = [
        'shipment_id',
        'from_status',
        'to_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'from_status' => ShipmentStatus::class,
        'to_status' => ShipmentStatus::class,
    ];

    // ========================================
    // Relationships
    // ========================================

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
