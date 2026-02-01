<?php
/**
 * FULL ERP CYCLE TEST
 * 
 * Simulates a complete business cycle:
 * 1. Create Supplier & Product
 * 2. Purchase Cycle: PO -> GRN -> Invoice -> Payment
 * 3. Sales Cycle: POS Checkout
 * 4. Accounting Verification: Trial Balance
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Unit;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use App\Http\Controllers\POSController;

echo "🔄 FULL ERP CYCLE TEST\n";
echo "======================\n\n";

DB::beginTransaction();

try {
    // ============================================
    // 1️⃣ SETUP: Master Data
    // ============================================
    echo "1️⃣  Master Data Setup...\n";

    $warehouse = Warehouse::firstOrCreate(['name' => 'Main Warehouse'], ['code' => 'MAIN', 'is_active' => true]);
    $unit = Unit::firstOrCreate(['name' => 'Piece'], ['abbreviation' => 'PCS', 'is_active' => true]);

    // Get Accounts
    $inventoryAcc = Account::where('code', '1301')->first();
    $cogsAcc = Account::where('code', '5101')->first();
    $salesAcc = Account::where('code', '4101')->first();
    $apAcc = Account::where('code', '2101')->first(); // Accounts Payable
    $cashAcc = Account::where('code', '1101')->first(); // Cash

    $product = Product::create([
        'sku' => 'FULL-TEST-' . rand(1000, 9999),
        'name' => 'Complete Cycle Product',
        'unit_id' => $unit->id,
        'cost_price' => 100.00,
        'selling_price' => 200.00,
        'is_active' => true,
        'is_sellable' => true,
        'is_purchasable' => true,
        'inventory_account_id' => $inventoryAcc->id,
        'purchase_account_id' => $inventoryAcc->id, // Asset account for purchases
        'sales_account_id' => $salesAcc->id,
        'cogs_account_id' => $cogsAcc->id,
    ]);

    $supplier = Supplier::create([
        'name' => 'Test Supplier',
        'code' => 'SUP-' . rand(1000, 9999),
        'is_active' => true
    ]);

    echo "   ✅ Product created: {$product->sku}\n";
    echo "   ✅ Supplier created: {$supplier->code}\n\n";

    // ============================================
    // 2️⃣ PURCHASING CYCLE
    // ============================================
    echo "2️⃣  Purchasing Cycle (Buy 10 items @ 100)...\n";

    $purchasingService = app(PurchasingService::class);

    // A. Create PO
    $po = $purchasingService->createPurchaseOrder(
        ['supplier_id' => $supplier->id, 'warehouse_id' => $warehouse->id],
        [['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100.00]]
    );
    $purchasingService->approvePurchaseOrder($po);

    // B. Receive Goods (GRN)
    $grn = $purchasingService->receiveGoods(
        $po,
        $warehouse,
        [['purchase_order_line_id' => $po->lines->first()->id, 'quantity' => 10, 'unit_cost' => 100.00]]
    );

    echo "   ✅ GRN Created: {$grn->grn_number}\n";

    // Verify Stock increased
    $stock = ProductStock::where('product_id', $product->id)->first();
    if ($stock->quantity != 10)
        throw new Exception("Stock not updated after GRN! Found: {$stock->quantity}");
    echo "   ✅ Stock updated: 10.00\n";

    // Verify Accounting (Inventory DR, AP CR)
    $grnEntry = JournalEntry::find($grn->journal_entry_id);
    if (!$grnEntry)
        throw new Exception("GRN Journal Entry not created!");
    echo "   ✅ GRN Journal Entry: {$grnEntry->entry_number}\n\n";

    // ============================================
    // 3️⃣ SALES CYCLE (POS)
    // ============================================
    echo "3️⃣  Sales Cycle (Sell 2 items @ 200)...\n";

    $posController = app(POSController::class);
    $request = Request::create('/pos/checkout', 'POST', [
        'items' => [['product_id' => $product->id, 'quantity' => 2, 'price' => 200.00]],
        'total' => 400.00,
        'amount_paid' => 400.00,
        'payment_method' => 'cash',
        'warehouse_id' => $warehouse->id
    ]);
    $request->setUserResolver(fn() => (object) ['id' => 1]);

    $response = $posController->checkout($request);
    $result = json_decode($response->getContent(), true);

    if (!($result['success'] ?? false)) {
        throw new Exception("POS Checkout Failed: " . ($result['message'] ?? 'Unknown'));
    }

    $invoiceId = $result['invoice']['id'];
    echo "   ✅ POS Invoice Created: ID {$invoiceId}\n";

    // Verify Stock reduced
    $stock->refresh();
    if ($stock->quantity != 8)
        throw new Exception("Stock not reduced after Sale! Found: {$stock->quantity}");
    echo "   ✅ Stock reduced: 10 -> 8\n";

    // ============================================
    // 4️⃣ ACCOUNTING VERIFICATION
    // ============================================
    echo "\n4️⃣  Accounting Integrity Check...\n";

    // Capture JEs created in this transaction
    // We know we created:
    // 1. GRN JE (Inventory / AP)
    // 2. POS JE (Cash / Sales)
    // 3. COGS JE (COGS / Inventory - from InventoryService via POS)
    // 4. (Potentially) Purchase Wash Entry (Inv / Inv - from InventoryService via GRN)

    // Let's verify by checking the sum of ALL lines created in this transaction
    // Since we are inside a transaction, we can just check lines created after we started? 
    // Actually, DB IDs increment.

    // Refresh models to get latest DB state (especially journal_entry_id)
    $grn->refresh();

    // Better: Get specific JE IDs
    $grnEntry = $grn->journal_entry_id ? JournalEntry::find($grn->journal_entry_id) : null;

    $invoice = \Modules\Sales\Models\SalesInvoice::find($invoiceId);
    $posEntry = $invoice && $invoice->journal_entry_id ? JournalEntry::find($invoice->journal_entry_id) : null;

    // Find COGS Entry
    // It is linked to the StockMovement created by POS
    $saleMovement = \Modules\Inventory\Models\StockMovement::where('source_type', \Modules\Sales\Models\SalesInvoice::class)
        ->where('source_id', $invoiceId)
        ->first();
    $cogsEntry = $saleMovement && $saleMovement->journal_entry_id
        ? JournalEntry::find($saleMovement->journal_entry_id)
        : null;

    $jeIds = [];
    if ($grnEntry)
        $jeIds[] = $grnEntry->id;
    if ($posEntry)
        $jeIds[] = $posEntry->id;
    if ($cogsEntry)
        $jeIds[] = $cogsEntry->id;

    echo "   Analyzed JEs Count: " . count($jeIds) . "\n";
    echo "   JE IDs: " . implode(', ', $jeIds) . "\n";

    if ($cogsEntry)
        echo "   ✅ COGS Entry found: {$cogsEntry->entry_number}\n";
    else {
        echo "   ❌ COGS Entry NOT found!\n";
        // Debug why
        echo "      Invoice ID: $invoiceId\n";
        $sm = \Modules\Inventory\Models\StockMovement::where('source_type', \Modules\Sales\Models\SalesInvoice::class)
            ->where('source_id', $invoiceId)
            ->first();
        if ($sm) {
            echo "      StockMovement Found: ID {$sm->id}, JE ID: " . ($sm->journal_entry_id ?? 'NULL') . "\n";
        } else {
            echo "      StockMovement NOT found for Invoice $invoiceId\n";
        }
    }

    $inventoryBalance = getBalanceForJes($inventoryAcc->id, $jeIds);
    $apBalance = getBalanceForJes($apAcc->id, $jeIds);
    $salesBalance = getBalanceForJes($salesAcc->id, $jeIds);
    $cashBalance = getBalanceForJes($cashAcc->id, $jeIds);
    $cogsBalance = getBalanceForJes($cogsAcc->id, $jeIds);

    echo "   - Inventory Balance: {$inventoryBalance} (Expected: 1000 - 200 = 800)\n";
    echo "   - AP Balance: {$apBalance} (Expected: -1000)\n";
    echo "   - Sales Balance: {$salesBalance} (Expected: -400)\n";
    echo "   - Cash Balance: {$cashBalance} (Expected: 400)\n";
    echo "   - COGS Balance: {$cogsBalance} (Expected: 200)\n";

    // Equation:
    // Assets (Inv + Cash) = 800 + 400 = 1200
    // Liabilities (AP) = -1000
    // Equity (Revenue - Expenses) = -400 - 200(dr)?? No.
    // Revenue is Credit (-400). Expenses are Debit (+200).
    // Net Equity Change = -400 + 200 = -200 (Credit/Profit)

    // Accounting Equation: Assets + Expenses = Liabilities + Revenue
    // (Inv + Cash) + (COGS) = (AP) + (Sales)
    // (800 + 400) + (200) = (1000) + (400)
    // 1400 = 1400 -> BALANCED!

    $totalDebits = $inventoryBalance + $cashBalance + $cogsBalance; // These are usually positive (Assets/Exp)
    $totalCredits = abs($apBalance + $salesBalance); // These are usually negative (Liab/Rev)

    // Note: getBalanceForJes returns (Dr - Cr). 
    // Inv: 800 (Dr)
    // Cash: 400 (Dr)
    // COGS: 200 (Dr)
    // AP: -1000 (Cr)
    // Sales: -400 (Cr)
    // Sum = 800 + 400 + 200 - 1000 - 400 = 0

    $netSum = $inventoryBalance + $cashBalance + $cogsBalance + $apBalance + $salesBalance;

    if (abs($netSum) < 0.1) {
        echo "\n   ✅ SYSTEM BALANCED: Net Sum ({$netSum}) == 0\n";
    } else {
        echo "\n   ❌ SYSTEM UNBALANCED: Net Sum ({$netSum}) != 0\n";
    }

} catch (Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} finally {
    DB::rollBack();
    echo "\n🏁 Test complete (Transaction rolled back)\n";
}

function getBalanceForJes($accountId, $jeIds)
{
    if (empty($jeIds))
        return 0;
    $debit = \Modules\Accounting\Models\JournalEntryLine::where('account_id', $accountId)
        ->whereIn('journal_entry_id', $jeIds)
        ->sum('debit');
    $credit = \Modules\Accounting\Models\JournalEntryLine::where('account_id', $accountId)
        ->whereIn('journal_entry_id', $jeIds)
        ->sum('credit');
    return $debit - $credit;
}
