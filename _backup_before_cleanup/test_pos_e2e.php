<?php
/**
 * End-to-End POS Test
 * 
 * Tests the complete POS flow:
 * 1. Product lookup
 * 2. Stock check
 * 3. Checkout (Invoice + Stock Reduction + Journal Entry)
 * 4. Verify all records created correctly
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Unit;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use App\Http\Controllers\POSController;

echo "🧪 END-TO-END POS TEST\n";
echo "======================\n\n";

DB::beginTransaction();

try {
    // ============================================
    // SETUP: Create test data
    // ============================================
    echo "1️⃣  Setting up test data...\n";

    $unit = Unit::firstOrCreate(['name' => 'قطعة'], ['abbreviation' => 'PCS', 'is_active' => true]);
    $warehouse = Warehouse::firstOrCreate(['name' => 'Main Warehouse'], ['code' => 'MAIN', 'is_active' => true]);
    $customer = Customer::firstOrCreate(['name' => 'نقدي'], ['code' => 'CASH', 'is_active' => true]);

    $product = Product::firstOrCreate(
        ['sku' => 'E2E-TEST-001'],
        [
            'name' => 'منتج اختبار E2E',
            'unit_id' => $unit->id,
            'cost_price' => 50.00,
            'selling_price' => 100.00,
            'is_active' => true,
            'is_sellable' => true,
        ]
    );

    // Add initial stock using InventoryService
    $inventoryService = app(\Modules\Inventory\Services\InventoryService::class);
    $inventoryService->addStock(
        $product,
        $warehouse,
        10.0,
        50.0,
        \Modules\Inventory\Enums\MovementType::ADJUSTMENT_IN,
        'E2E-INIT',
        'Initial stock for E2E test'
    );

    $initialStock = ProductStock::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();

    echo "   ✅ Product: {$product->name} (SKU: {$product->sku})\n";
    echo "   ✅ Initial Stock: {$initialStock->quantity}\n\n";

    // ============================================
    // TEST: Simulate POS Checkout
    // ============================================
    echo "2️⃣  Simulating POS Checkout...\n";

    // Count records before
    $invoicesBefore = SalesInvoice::count();
    $movementsBefore = StockMovement::where('product_id', $product->id)->count();
    $journalsBefore = JournalEntry::count();

    // Create a mock request
    $checkoutData = [
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => 100.00,
                'discount' => 0,
            ]
        ],
        'subtotal' => 200.00,
        'discount' => 0,
        'tax' => 0,
        'total' => 200.00,
        'payment_method' => 'cash',
        'amount_paid' => 200.00,
    ];

    $request = Request::create('/pos/checkout', 'POST', $checkoutData);
    $request->setUserResolver(fn() => (object) ['id' => 1]); // Mock user

    // Get the controller with dependencies
    $controller = app(POSController::class);

    // Call checkout directly (bypass HTTP layer)
    $response = $controller->checkout($request);
    $result = json_decode($response->getContent(), true);

    if ($result['success'] ?? false) {
        echo "   ✅ Checkout Successful!\n";
        echo "   Invoice ID: {$result['invoice']['id']}\n";
        echo "   Invoice Number: " . ($result['invoice']['invoice_number'] ?? $result['invoice']['number'] ?? 'N/A') . "\n";
        echo "   Total: " . ($result['invoice']['total'] ?? 'N/A') . "\n\n";
    } else {
        echo "   ❌ Checkout Failed: " . ($result['message'] ?? 'Unknown error') . "\n\n";
        throw new Exception('Checkout failed');
    }

    // ============================================
    // VERIFY: Check all records created
    // ============================================
    echo "3️⃣  Verifying records...\n";

    // Check Invoice
    $invoicesAfter = SalesInvoice::count();
    if ($invoicesAfter > $invoicesBefore) {
        echo "   ✅ SalesInvoice created\n";
    } else {
        echo "   ❌ SalesInvoice NOT created\n";
    }

    // Check Invoice Lines
    $invoice = SalesInvoice::find($result['invoice']['id']);
    $lineCount = $invoice->lines()->count();
    if ($lineCount > 0) {
        echo "   ✅ SalesInvoiceLines created ({$lineCount} lines)\n";
    } else {
        echo "   ❌ SalesInvoiceLines NOT created\n";
    }

    // Check Stock Movement
    $movementsAfter = StockMovement::where('product_id', $product->id)->count();
    if ($movementsAfter > $movementsBefore) {
        $movement = StockMovement::where('product_id', $product->id)
            ->where('source_type', SalesInvoice::class)
            ->latest()
            ->first();
        echo "   ✅ StockMovement created (Qty: {$movement->quantity})\n";
    } else {
        echo "   ❌ StockMovement NOT created\n";
    }

    // Check Stock Reduced
    $currentStock = ProductStock::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();
    $expectedStock = $initialStock->quantity - 2;
    if (abs($currentStock->quantity - $expectedStock) < 0.01) {
        echo "   ✅ Stock reduced correctly: {$initialStock->quantity} → {$currentStock->quantity}\n";
    } else {
        echo "   ⚠️  Stock unexpected: {$currentStock->quantity} (expected: {$expectedStock})\n";
    }

    // Check Journal Entry
    $journalsAfter = JournalEntry::count();
    if ($journalsAfter > $journalsBefore) {
        $je = JournalEntry::find($invoice->journal_entry_id);
        echo "   ✅ JournalEntry created: {$je->entry_number}\n";

        // Check balance
        $debit = $je->lines()->sum('debit');
        $credit = $je->lines()->sum('credit');
        if (abs($debit - $credit) < 0.01) {
            echo "   ✅ Journal balanced: Debit={$debit}, Credit={$credit}\n";
        } else {
            echo "   ❌ Journal UNBALANCED: Debit={$debit}, Credit={$credit}\n";
        }
    } else {
        echo "   ❌ JournalEntry NOT created\n";
    }

    // ============================================
    // INTEGRITY: Verify SUM(movements) == stock
    // ============================================
    echo "\n4️⃣  Integrity Check...\n";

    $sumMovements = StockMovement::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->sum('quantity');
    $stockQty = $currentStock->quantity;

    if (abs($sumMovements - $stockQty) < 0.01) {
        echo "   ✅ INTEGRITY PASSED: SUM(movements)={$sumMovements} == stock={$stockQty}\n";
    } else {
        echo "   ❌ INTEGRITY FAILED: SUM(movements)={$sumMovements} != stock={$stockQty}\n";
    }

    echo "\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} finally {
    DB::rollBack();
    echo "🏁 Test complete (Transaction rolled back)\n";
}
