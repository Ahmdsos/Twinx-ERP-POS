<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Loyalty Settings Model
 */
class LoyaltySettings extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Calculate points for purchase amount
     */
    public static function calculatePoints(float $amount): int
    {
        $pointsPerAmount = (float) static::getValue('points_per_amount', 1);
        $amountPerPoint = (float) static::getValue('amount_per_point', 10);

        return (int) floor(($amount / $amountPerPoint) * $pointsPerAmount);
    }

    /**
     * Calculate monetary value of points
     */
    public static function calculatePointsValue(int $points): float
    {
        $pointValue = (float) static::getValue('points_value', 0.1);
        return $points * $pointValue;
    }

    /**
     * Check if points can be redeemed
     */
    public static function canRedeem(int $points): bool
    {
        $minPoints = (int) static::getValue('min_redeem_points', 100);
        return $points >= $minPoints;
    }
}
