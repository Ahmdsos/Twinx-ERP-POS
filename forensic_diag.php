<?php

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalLine;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Forensic Diagnostic Suite (Phase B) ---\n\n";

// 1. Accounting Imbalance Check (B-1)
echo "[B-1] Checking for Accounting Imbalances...\n";
$imbalances = DB::table('journal_entry_lines')
    ->select('journal_entry_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
    ->groupBy('journal_entry_id')
    ->havingRaw('ABS(SUM(debit) - SUM(credit)) > 0.001')
    ->get();

if ($imbalances->isEmpty()) {
    echo "SUCCESS: All journal entries are balanced.\n";
} else {
    echo "WARNING: " . $imbalances->count() . " imbalanced entries found!\n";
    foreach ($imbalances as $imbalance) {
        echo " - Entry #{$imbalance->journal_entry_id}: Debit={$imbalance->total_debit}, Credit={$imbalance->total_credit}\n";
    }
}

echo "\n[B-1] Checking for Orphaned Journal Lines...\n";
$orphans = DB::table('journal_entry_lines')
    ->whereNotExists(function ($query) {
        $query->select(DB::raw(1))
            ->from('journal_entries')
            ->whereRaw('journal_entries.id = journal_entry_lines.journal_entry_id');
    })->count();
echo "Orphaned Lines: $orphans\n";

// 2. Inventory SSOT Check (B-2)
echo "\n[B-2] Checking Inventory SSOT Consistency...\n";
$stockDiscrepancies = 0;
$stocks = ProductStock::all();
foreach ($stocks as $stock) {
    $movementSum = StockMovement::where('product_id', $stock->product_id)
        ->where('warehouse_id', $stock->warehouse_id)
        ->sum('quantity');

    if (abs($movementSum - $stock->quantity) > 0.0001) {
        $stockDiscrepancies++;
        echo " - Discrepancy Found | Product ID: {$stock->product_id} | Warehouse: {$stock->warehouse_id}\n";
        echo "   Current Total: {$stock->quantity} | Movement Sum: {$movementSum}\n";
    }
}

if ($stockDiscrepancies == 0) {
    echo "SUCCESS: Inventory totals match movement history.\n";
} else {
    echo "WARNING: $stockDiscrepancies stock discrepancies found!\n";
}

echo "\n--- Diagnostic Complete ---\n";
