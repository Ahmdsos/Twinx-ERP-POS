<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Courier Model - شركات الشحن
 * 
 * Manages shipping/delivery companies for order fulfillment
 */
class Courier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tracking_url_template', // e.g., https://track.courier.com/{tracking_number}
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // ========================================
    // Relationships
    // ========================================

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function deliveryOrders(): HasMany
    {
        return $this->hasMany(DeliveryOrder::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ========================================
    // Business Methods
    // ========================================

    /**
     * Generate tracking URL for a shipment
     */
    public function getTrackingUrl(?string $trackingNumber): ?string
    {
        if (!$this->tracking_url_template || !$trackingNumber) {
            return null;
        }

        return str_replace('{tracking_number}', $trackingNumber, $this->tracking_url_template);
    }
}
