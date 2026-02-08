<?php

namespace Modules\Core\Traits;

use App\Models\Setting;

/**
 * Trait HasTaxCalculations
 * Centralized logic for tax-inclusive/exclusive and total calculations
 */
trait HasTaxCalculations
{
    /**
     * Calculate tax and totals for a line item
     */
    public function calculateLineTax(float $quantity, float $price, float $discount = 0, ?float $taxRate = null): array
    {
        // 1. Get Settings - Use 14% as default to match frontend and SettingsSeeder
        $globalTax = (float) Setting::getValue('default_tax_rate', 14);
        $taxRate = $taxRate ?? $globalTax;
        $isInclusive = (bool) Setting::getValue('tax_inclusive', false);

        // 2. Base Calculation (Line Amount before Tax & after Line Discount)
        $grossBeforeTax = ($quantity * $price) - $discount;

        if ($isInclusive) {
            // Price is Gross (Inclusive of Tax)
            // Net = Gross / (1 + Rate/100)
            $lineGross = round($grossBeforeTax, 2);
            $lineNet = round($lineGross / (1 + ($taxRate / 100)), 4);
            $taxAmount = round($lineGross - $lineNet, 2);

            $unitPriceNet = round($price / (1 + ($taxRate / 100)), 4);
        } else {
            // Price is Net (Exclusive of Tax)
            // Tax = Net * Rate/100
            $lineNet = round($grossBeforeTax, 4);
            $taxAmount = round(($lineNet * $taxRate) / 100, 2);
            $lineGross = round($lineNet + $taxAmount, 2);

            $unitPriceNet = round($price, 4);
        }

        return [
            'quantity' => $quantity,
            'unit_price_net' => $unitPriceNet,
            'discount_amount' => $discount,
            'tax_percent' => $taxRate,
            'tax_amount' => $taxAmount,
            'line_total' => $lineGross, // Total Gross for the line
            'subtotal' => $lineNet,    // Total Net for the line
            'is_inclusive' => $isInclusive
        ];
    }
}
