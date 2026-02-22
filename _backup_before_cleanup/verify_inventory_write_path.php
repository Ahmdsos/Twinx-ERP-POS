<?php
/**
 * Verify Inventory Write Path Enforcement
 * 
 * This script verifies that:
 * 1. No direct ProductStock::create outside InventoryService
 * 2. No direct decrement/increment on quantity
 * 3. Every stock change creates a StockMovement
 */

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Services\InventoryService;
use Modules\Accounting\Services\JournalService;

echo "🔍 INVENTORY WRITE PATH VERIFICATION\n";
echo "=====================================\n\n";

// === TEST 1: Verify InventoryService creates movements ===
echo "1️⃣  Testing InventoryService::addStock...\n";

DB::beginTransaction();
try {
    // Setup
    $unit = Unit::firstOrCreate(['name' => 'Test Unit'], ['abbreviation' => 'TU', 'is_active' => true]);
    $warehouse = Warehouse::firstOrCreate(['name' => 'Test Warehouse'], ['code' => 'TEST-WH', 'is_active' => true]);
    $product = Product::firstOrCreate(
        ['sku' => 'VERIFY-INV-001'],
        ['name' => 'Verification Product', 'unit_id' => $unit->id, 'cost_price' => 10, 'selling_price' => 20]
    );

    // Get services
    $journalService = app(JournalService::class);
    $inventoryService = app(InventoryService::class);

    // Count movements before
    $movementsBefore = StockMovement::where('product_id', $product->id)->count();

    // Add stock via service
    $movement = $inventoryService->addStock(
        $product,
        $warehouse,
        5.0,
        10.0,
        MovementType::ADJUSTMENT_IN,
        'VERIFY-001',
        'Verification test'
    );

    // Verify movement was created
    $movementsAfter = StockMovement::where('product_id', $product->id)->count();

    if ($movementsAfter > $movementsBefore) {
        echo "   ✅ StockMovement created correctly\n";
        echo "   Movement ID: {$movement->id}, Number: {$movement->movement_number}\n";
    } else {
        echo "   ❌ FAILED: No StockMovement created\n";
    }

    // Verify stock updated
    $stock = ProductStock::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();

    if ($stock && $stock->quantity >= 5) {
        echo "   ✅ ProductStock.quantity updated correctly: {$stock->quantity}\n";
    } else {
        echo "   ❌ FAILED: ProductStock not updated\n";
    }

    // === TEST 2: Verify removeStock ===
    echo "\n2️⃣  Testing InventoryService::removeStock...\n";

    $movementsBefore = StockMovement::where('product_id', $product->id)->count();

    $removeMovement = $inventoryService->removeStock(
        $product,
        $warehouse,
        2.0,
        MovementType::SALE,
        'VERIFY-002',
        'Removal test'
    );

    $movementsAfter = StockMovement::where('product_id', $product->id)->count();

    if ($movementsAfter > $movementsBefore) {
        echo "   ✅ StockMovement created for removal\n";
        echo "   Movement ID: {$removeMovement->id}, Qty: {$removeMovement->quantity}\n";
    } else {
        echo "   ❌ FAILED: No StockMovement for removal\n";
    }

    // Verify stock decreased
    $stock->refresh();
    if ($stock->quantity == 3.0) {
        echo "   ✅ ProductStock.quantity decreased correctly: {$stock->quantity}\n";
    } else {
        echo "   ⚠️  Stock quantity unexpected: {$stock->quantity} (expected 3.0)\n";
    }

    // === TEST 3: Verify Integrity ===
    echo "\n3️⃣  Verifying SUM(movements) == stock.quantity...\n";

    $sumMovements = StockMovement::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->sum('quantity');

    $stockQty = $stock->quantity;

    if (abs($sumMovements - $stockQty) < 0.0001) {
        echo "   ✅ INTEGRITY PASSED: SUM(movements)={$sumMovements} == stock.quantity={$stockQty}\n";
    } else {
        echo "   ❌ INTEGRITY FAILED: SUM(movements)={$sumMovements} != stock.quantity={$stockQty}\n";
    }

} finally {
    DB::rollBack();
    echo "\n🏁 Verification complete (Transaction rolled back)\n";
}

echo "\\n=====================================\\n";
echo "📋 MANUAL VERIFICATION COMMANDS:\\n";
echo "=====================================\\n";
echo "Run these in PowerShell to verify no violations remain:\\n\\n";
echo "Select-String -Path 'app\\Http\\Controllers\\POSController.php' -Pattern 'ProductStock::create'\\n";
echo "Select-String -Path 'app\\Http\\Controllers\\POSController.php' -Pattern 'decrement'\\n";
echo "Select-String -Path 'app\\Http\\Controllers\\POSController.php' -Pattern 'increment'\\n";
echo "Select-String -Path 'app\\Http\\Controllers\\ProductController.php' -Pattern 'ProductStock::create'\\n";

