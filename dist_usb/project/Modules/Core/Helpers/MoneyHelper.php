<?php

namespace Modules\Core\Helpers;

/**
 * MoneyHelper - Utility class for monetary calculations
 * 
 * All monetary calculations should go through this class to ensure
 * consistent precision and rounding across the ERP.
 */
class MoneyHelper
{
    /**
     * Get decimal places from config
     */
    public static function getDecimalPlaces(): int
    {
        return config('erp.currency.decimal_places', 2);
    }

    /**
     * Round a monetary value to the configured decimal places
     */
    public static function round(float $value): float
    {
        return round($value, self::getDecimalPlaces());
    }

    /**
     * Format a value as currency string
     */
    public static function format(float $value, ?string $currency = null): string
    {
        $currency = $currency ?? config('erp.currency.default', 'EGP');
        $decimalPlaces = self::getDecimalPlaces();
        $thousandSeparator = config('erp.currency.thousand_separator', ',');
        $decimalSeparator = config('erp.currency.decimal_separator', '.');

        $formatted = number_format(
            $value,
            $decimalPlaces,
            $decimalSeparator,
            $thousandSeparator
        );

        return "{$formatted} {$currency}";
    }

    /**
     * Check if two monetary values are equal (accounting for floating point precision)
     */
    public static function equals(float $a, float $b): bool
    {
        $precision = pow(10, -self::getDecimalPlaces());
        return abs($a - $b) < $precision;
    }

    /**
     * Check if a value is zero (accounting for floating point precision)
     */
    public static function isZero(float $value): bool
    {
        return self::equals($value, 0.0);
    }

    /**
     * Calculate percentage of a value
     */
    public static function percentage(float $value, float $percentage): float
    {
        return self::round($value * ($percentage / 100));
    }

    /**
     * Add tax to a value
     */
    public static function addTax(float $value, float $taxRate): float
    {
        return self::round($value + self::percentage($value, $taxRate));
    }

    /**
     * Calculate line total (quantity * unit price - discount)
     */
    public static function lineTotal(
        float $quantity,
        float $unitPrice,
        float $discountPercent = 0,
        float $taxPercent = 0
    ): array {
        $subtotal = self::round($quantity * $unitPrice);
        $discount = self::percentage($subtotal, $discountPercent);
        $taxable = self::round($subtotal - $discount);
        $tax = self::percentage($taxable, $taxPercent);
        $total = self::round($taxable + $tax);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'taxable' => $taxable,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
