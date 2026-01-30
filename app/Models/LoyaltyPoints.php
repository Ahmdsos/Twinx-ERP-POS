<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Sales\Models\Customer;

/**
 * Loyalty Points Balance Model
 */
class LoyaltyPoints extends Model
{
    protected $fillable = [
        'customer_id',
        'total_earned',
        'total_redeemed',
        'current_balance',
        'tier',
        'tier_expiry',
    ];

    protected $casts = [
        'tier_expiry' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'customer_id', 'customer_id');
    }

    /**
     * Add points to customer balance
     */
    public function addPoints(int $points, string $description, ?string $refType = null, ?int $refId = null): LoyaltyTransaction
    {
        $this->increment('total_earned', $points);
        $this->increment('current_balance', $points);
        $this->updateTier();

        return LoyaltyTransaction::create([
            'customer_id' => $this->customer_id,
            'type' => 'earn',
            'points' => $points,
            'balance_after' => $this->current_balance,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'description' => $description,
        ]);
    }

    /**
     * Redeem points from customer balance
     */
    public function redeemPoints(int $points, string $description, ?string $refType = null, ?int $refId = null): ?LoyaltyTransaction
    {
        if ($this->current_balance < $points) {
            return null; // Not enough points
        }

        $this->increment('total_redeemed', $points);
        $this->decrement('current_balance', $points);

        return LoyaltyTransaction::create([
            'customer_id' => $this->customer_id,
            'type' => 'redeem',
            'points' => -$points,
            'balance_after' => $this->current_balance,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'description' => $description,
        ]);
    }

    /**
     * Calculate points value in currency
     */
    public function getPointsValue(): float
    {
        $pointValue = LoyaltySettings::getValue('points_value', 0.1);
        return $this->current_balance * $pointValue;
    }

    /**
     * Update tier based on total earned
     */
    public function updateTier(): void
    {
        $tier = 'bronze';

        if ($this->total_earned >= 50000) {
            $tier = 'platinum';
        } elseif ($this->total_earned >= 20000) {
            $tier = 'gold';
        } elseif ($this->total_earned >= 5000) {
            $tier = 'silver';
        }

        if ($this->tier !== $tier) {
            $this->tier = $tier;
            $this->tier_expiry = now()->addYear();
            $this->save();
        }
    }

    /**
     * Get or create loyalty record for customer
     */
    public static function getOrCreate(int $customerId): self
    {
        return static::firstOrCreate(
            ['customer_id' => $customerId],
            ['total_earned' => 0, 'total_redeemed' => 0, 'current_balance' => 0, 'tier' => 'bronze']
        );
    }

    /**
     * Get tier badge class
     */
    public function getTierBadgeClass(): string
    {
        return match ($this->tier) {
            'platinum' => 'bg-dark',
            'gold' => 'bg-warning text-dark',
            'silver' => 'bg-secondary',
            default => 'bg-danger',
        };
    }
}
