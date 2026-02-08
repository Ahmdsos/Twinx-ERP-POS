<?php

use Illuminate\Support\Facades\DB;
use Modules\Core\Traits\HasTaxCalculations;
use App\Models\Setting;

// Booth Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Mock class to test trait
 */
class TaxTester
{
    use HasTaxCalculations;
}

$tester = new TaxTester();

echo "--- Testing Precision & Truth ---\n";

// Scenario 1: Tax Inclusive (14%) with "difficult" amount
// 100 EGP Inclusive. 
// Net = 100 / 1.14 = 87.7192982456...
// Net (rounded 4) = 87.7193
// Tax = 100 - 87.7193 = 12.2807 -> Round(2) = 12.28
// Line Total = 87.7193 + 12.28 = 99.9993 -> Round(2) = 100.00 (Balanced!)

$results = $tester->calculateLineTax(1, 100.00, 0, 14);

echo "Scenario 1 (100 EGP Inc 14%):\n";
echo "Line Total: " . $results['line_total'] . " (Expected 100.00)\n";
echo "Tax Amount: " . $results['tax_amount'] . "\n";
echo "Subtotal (Net): " . $results['subtotal'] . "\n";

$checkBalance = round($results['subtotal'] + $results['tax_amount'], 2);
echo "Balanced Check (Net + Tax rounded to 2): " . $checkBalance . "\n";

if ($checkBalance == round($results['line_total'], 2)) {
    echo "✅ SUCCESS: Precision is mathematically sound.\n";
} else {
    echo "❌ FAILURE: Balance drift detected: " . abs($checkBalance - $results['line_total']) . "\n";
}

echo "\n--- End of Verification ---\n";
