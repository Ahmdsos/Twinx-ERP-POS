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
        // 1. Get Settings
        $globalTax = (float) Setting::getValue('default_tax_rate', 0);
        $taxRate = $taxRate ?? $globalTax;
        $isInclusive = (bool) Setting::getValue('tax_inclusive', false);

        // 2. Base Calculation (Line Amount before Tax & after Line Discount)
        $grossBeforeTax = ($quantity * $price) - $discount;

        if ($isInclusive) {
            // Price is Gross (Inclusive of Tax)
            // Net = Gross / (1 + Rate/100)
            $lineGross = $grossBeforeTax;
            $lineNet = $lineGross / (1 + ($taxRate / 100));
            $taxAmount = $lineGross - $lineNet;

            $unitPriceNet = $price / (1 + ($taxRate / 100));
        } else {
            // Price is Net (Exclusive of Tax)
            // Tax = Net * Rate/100
            $lineNet = $grossBeforeTax;
            $taxAmount = ($lineNet * $taxRate) / 100;
            $lineGross = $lineNet + $taxAmount;

            $unitPriceNet = $price;
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
