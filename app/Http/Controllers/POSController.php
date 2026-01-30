<?php

namespace App\Http\Controllers;

use App\Services\BarcodeService;
use App\Models\PosShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Accounting\Models\Account;

use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Services\JournalService;
use Modules\Inventory\Services\InventoryService;

/**
 * POSController
 * Full Point of Sale system with touch-friendly interface
 */
class POSController extends Controller
{
    public function __construct(
        protected JournalService $journalService,
        protected InventoryService $inventoryService
    ) {
    }
    /**
     * POS main interface
     */
    public function index()
    {
        $categories = Category::active()->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get(['id', 'code', 'name']);
        $warehouses = Warehouse::all();
        $activeShift = PosShift::getActiveShift();

        return view('pos.index', compact('categories', 'customers', 'warehouses', 'activeShift'));
    }

    /**
     * Search products (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $products = Product::query()
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with(['category:id,name', 'unit:id,name,abbreviation', 'images'])
            ->limit(50)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'name' => $p->name,
                'price' => (float) $p->selling_price,
                'cost' => (float) $p->cost_price,
                'tax_rate' => (float) $p->tax_rate,
                'stock' => $p->getTotalStock(),
                'category' => $p->category?->name,
                'unit' => $p->unit?->abbreviation ?? 'PCS',
                'image' => $p->primaryImageUrl,
            ]);

        return response()->json($products);
    }

    /**
     * Find product by barcode (AJAX)
     */
    public function findByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');

        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->with(['unit:id,name,abbreviation', 'images'])
            ->first();

        if (!$product) {
            return response()->json(['error' => 'المنتج غير موجود'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'price' => (float) $product->selling_price,
            'cost' => (float) $product->cost_price,
            'tax_rate' => (float) $product->tax_rate,
            'stock' => $product->getTotalStock(),
            'unit' => $product->unit?->abbreviation ?? 'PCS',
            'image' => $product->primaryImageUrl,
        ]);
    }

    /**
     * Process sale (checkout)
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,card,credit,split',
            'amount_paid' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['price'] - ($item['discount'] ?? 0);
                $subtotal += $lineTotal;
            }

            $discount = $request->get('discount', 0);
            $total = $subtotal - $discount;
            $amountPaid = $request->amount_paid;
            $change = max(0, $amountPaid - $total);
            $balanceDue = max(0, $total - $amountPaid);

            // Generate invoice number
            $lastInvoice = SalesInvoice::whereDate('invoice_date', today())->orderByDesc('id')->first();
            $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;
            $invoiceNumber = 'POS-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Get or create walk-in customer if no customer selected
            $customerId = $request->customer_id;
            if (!$customerId) {
                $walkInCustomer = Customer::firstOrCreate(
                    ['email' => 'walkin@pos.local'],
                    [
                        'name' => 'عميل نقدي (Walk-in)',
                        'phone' => '0000000000',
                        'is_active' => true,
                    ]
                );
                $customerId = $walkInCustomer->id;
            }

            // Create invoice
            $invoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customerId,
                'invoice_date' => now(),
                'due_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $totalTax,
                'total' => $total,
                'paid_amount' => min($amountPaid, $total), // Don't exceed total
                'balance_due' => $balanceDue,
                'status' => $balanceDue > 0 ? 'partial' : 'paid',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'pos_shift_id' => PosShift::getActiveShift()?->id,
                'payment_method' => $request->payment_method,
            ]);

            // Update shift totals if active shift exists
            $activeShift = PosShift::getActiveShift();
            if ($activeShift) {
                $activeShift->incrementSales($total, $request->payment_method);
            }


            // Create invoice lines and update stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $lineTotal = $item['quantity'] * $item['price'] - ($item['discount'] ?? 0);

                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'discount_amount' => $item['discount'] ?? 0,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'line_total' => $lineTotal,
                ]);

                // Reduce stock via InventoryService (handles movements and COGS)
                $warehouse = Warehouse::find($request->warehouse_id) ?? Warehouse::first();
                $this->inventoryService->removeStock(
                    $product,
                    $warehouse,
                    $item['quantity'],
                    \Modules\Inventory\Enums\MovementType::SALE,
                    $invoice->invoice_number,
                    'POS Sale',
                    SalesInvoice::class,
                    $invoice->id
                );
            }

            // Create Journal Entry via JournalService
            $this->createPosJournalEntry($invoice, $request->payment_method);

            DB::commit();

            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'total' => $total,
                    'amount_paid' => $amountPaid,
                    'change' => $change,
                    'balance_due' => $balanceDue,
                ],
                'message' => 'تم إتمام عملية البيع بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create journal entry for POS sale using JournalService
     */
    protected function createPosJournalEntry(SalesInvoice $invoice, string $paymentMethod): void
    {
        // AR or Cash/Bank account
        $cashAccount = Account::where('code', '1001')->first(); // Cash
        $bankAccount = Account::where('code', '1002')->first(); // Bank
        $salesAccount = Account::where('code', '4101')->first(); // Sales Revenue
        $taxAccount = Account::where('code', '2105')->first(); // Tax Payable

        if (!$salesAccount)
            return;

        $debitAccountId = ($paymentMethod === 'card' || $paymentMethod === 'bank')
            ? ($bankAccount?->id ?? $cashAccount?->id)
            : $cashAccount?->id;

        if (!$debitAccountId)
            return;

        $lines = [
            ['account_id' => $debitAccountId, 'debit' => $invoice->total, 'credit' => 0],
            ['account_id' => $salesAccount->id, 'debit' => 0, 'credit' => $invoice->subtotal],
        ];

        if ($taxAccount && $invoice->tax_amount > 0) {
            $lines[] = ['account_id' => $taxAccount->id, 'debit' => 0, 'credit' => $invoice->tax_amount];
        }

        $entry = $this->journalService->create([
            'entry_date' => $invoice->invoice_date,
            'reference' => $invoice->invoice_number,
            'description' => "POS Sale to {$invoice->customer->name}",
            'source_type' => SalesInvoice::class,
            'source_id' => $invoice->id,
        ], $lines);

        // Auto-post the entry to affect balances immediately
        $this->journalService->post($entry);

        $invoice->update(['journal_entry_id' => $entry->id]);
    }

    /**
     * Create journal entry for POS sale
     * Debits: Cash/Bank, Credits: Sales Revenue
     */
    protected function createJournalEntry(SalesInvoice $invoice, string $paymentMethod): void
    {
        // Find accounts
        $cashAccount = Account::where('code', '1001')->first(); // Cash
        $bankAccount = Account::where('code', '1002')->first(); // Bank
        $salesAccount = Account::where('code', '4001')->first(); // Sales Revenue

        if (!$salesAccount)
            return; // Skip if accounts not set up

        $debitAccount = ($paymentMethod === 'card' || $paymentMethod === 'bank')
            ? ($bankAccount ?? $cashAccount)
            : $cashAccount;

        if (!$debitAccount)
            return;

        // Create journal entry (simplified - would use JournalEntry model if exists)
        DB::table('journal_entries')->insert([
            'entry_date' => now(),
            'reference_type' => SalesInvoice::class,
            'reference_id' => $invoice->id,
            'description' => 'POS Sale: ' . $invoice->invoice_number,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $entryId = DB::getPdo()->lastInsertId();

        // Debit Cash/Bank
        DB::table('journal_entry_lines')->insert([
            'journal_entry_id' => $entryId,
            'account_id' => $debitAccount->id,
            'debit' => $invoice->total,
            'credit' => 0,
            'description' => 'Cash received from POS sale',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Credit Sales Revenue
        DB::table('journal_entry_lines')->insert([
            'journal_entry_id' => $entryId,
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => $invoice->total,
            'description' => 'Sales revenue from POS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Show POS Returns page (GET)
     */
    public function showReturns(Request $request)
    {
        $invoiceNumber = $request->get('invoice');
        $invoice = null;

        if ($invoiceNumber) {
            $invoice = SalesInvoice::where('invoice_number', $invoiceNumber)
                ->with(['lines.product', 'customer'])
                ->first();
        }

        return view('pos.returns', compact('invoice', 'invoiceNumber'));
    }

    /**
     * Show Daily Report page (GET)
     */
    public function showDailyReport()
    {
        $today = today();
        $shift = PosShift::getActiveShift();

        $sales = SalesInvoice::whereDate('invoice_date', $today)
            ->where('invoice_number', 'like', 'POS-%')
            ->get();

        $summary = [
            'total_sales' => $sales->sum('total'),
            'sales_count' => $sales->count(),
            'cash_sales' => $sales->where('payment_method', 'cash')->sum('total'),
            'card_sales' => $sales->where('payment_method', 'card')->sum('total'),
            'credit_sales' => $sales->where('payment_method', 'credit')->sum('total'),
            'total_discounts' => $sales->sum('discount_amount'),
            'shift' => $shift,
        ];

        return view('pos.daily-report', compact('summary'));
    }

    /**
     * Process Sales Return from POS (POST)
     */
    public function salesReturn(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
            'items' => 'required|array|min:1',
            'items.*.line_id' => 'required|exists:sales_invoice_lines,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $originalInvoice = SalesInvoice::findOrFail($request->invoice_id);

            // Generate return number
            $lastReturn = SalesInvoice::where('invoice_number', 'like', 'RET-%')
                ->whereDate('invoice_date', today())
                ->orderByDesc('id')
                ->first();
            $sequence = $lastReturn ? (int) substr($lastReturn->invoice_number, -4) + 1 : 1;
            $returnNumber = 'RET-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $returnTotal = 0;
            $returnLines = [];

            foreach ($request->items as $item) {
                $originalLine = SalesInvoiceLine::find($item['line_id']);
                if (!$originalLine)
                    continue;

                $lineTotal = $originalLine->unit_price * $item['quantity'];
                // Apply proportional discount if any
                if ($originalLine->discount_amount > 0 && $originalLine->quantity > 0) {
                    $itemDiscount = ($originalLine->discount_amount / $originalLine->quantity) * $item['quantity'];
                    $lineTotal -= $itemDiscount;
                }

                $returnTotal += $lineTotal;

                $returnLines[] = [
                    'product_id' => $originalLine->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $originalLine->unit_price,
                    'line_total' => $lineTotal,
                ];

                // Restore stock via InventoryService
                $this->restoreStock($originalLine->product_id, $item['quantity'], $returnNumber, $originalInvoice);
            }

            // Create return record as a negative invoice
            $returnInvoice = SalesInvoice::create([
                'invoice_number' => $returnNumber,
                'customer_id' => $originalInvoice->customer_id,
                'invoice_date' => now(),
                'due_date' => now(),
                'subtotal' => -$returnTotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => -$returnTotal,
                'paid_amount' => -$returnTotal,
                'balance_due' => 0,
                'status' => 'refunded',
                'notes' => 'مرتجع من فاتورة: ' . $originalInvoice->invoice_number . ($request->reason ? ' - السبب: ' . $request->reason : ''),
                'created_by' => auth()->id(),
                'pos_shift_id' => PosShift::getActiveShift()?->id,
                'payment_method' => 'cash', // Returns are usually cash
            ]);

            // Update shift totals (decrement)
            $activeShift = PosShift::getActiveShift();
            if ($activeShift) {
                $activeShift->incrementSales(-$returnTotal, 'cash');
            }

            foreach ($returnLines as $line) {
                SalesInvoiceLine::create([
                    'sales_invoice_id' => $returnInvoice->id,
                    'product_id' => $line['product_id'],
                    'quantity' => -$line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => 0,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'line_total' => -$line['line_total'],
                ]);
            }

            // Create Reversal Accounting Entry
            $this->createPosReturnJournalEntry($returnInvoice);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إرجاع المنتجات بنجاح',
                'return_number' => $returnNumber,
                'return_total' => $returnTotal,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create journal entry for POS return
     */
    protected function createPosReturnJournalEntry(SalesInvoice $returnInvoice): void
    {
        $cashAccount = Account::where('code', '1001')->first(); // Cash
        $salesAccount = Account::where('code', '4101')->first(); // Sales Revenue

        if (!$cashAccount || !$salesAccount)
            return;

        // Swapped DR/CR from sale: DR Sales Revenue, CR Cash
        $lines = [
            ['account_id' => $salesAccount->id, 'debit' => abs($returnInvoice->total), 'credit' => 0],
            ['account_id' => $cashAccount->id, 'debit' => 0, 'credit' => abs($returnInvoice->total)],
        ];

        $entry = $this->journalService->create([
            'entry_date' => $returnInvoice->invoice_date,
            'reference' => $returnInvoice->invoice_number,
            'description' => $returnInvoice->notes,
            'source_type' => SalesInvoice::class,
            'source_id' => $returnInvoice->id,
        ], $lines);

        $this->journalService->post($entry);
        $returnInvoice->update(['journal_entry_id' => $entry->id]);
    }

    /**
     * Restore stock for returns using InventoryService
     */
    protected function restoreStock(int $productId, float $quantity, string $reference, ?SalesInvoice $invoice = null): void
    {
        $product = Product::find($productId);
        if (!$product)
            return;

        // Try to find original warehouse if invoice is provided, else use first warehouse
        $warehouseId = $invoice?->warehouse_id ?? Warehouse::first()?->id;
        $warehouse = Warehouse::find($warehouseId);

        if ($warehouse) {
            $this->inventoryService->addStock(
                $product,
                $warehouse,
                $quantity,
                $product->cost_price,
                \Modules\Inventory\Enums\MovementType::RETURN_IN,
                $reference,
                'POS Return',
                SalesInvoice::class,
                $invoice?->id
            );
        }
    }

    /**
     * Get receipt for printing
     */
    public function receipt(SalesInvoice $invoice)
    {
        $invoice->load(['customer', 'lines.product']);

        return view('pos.receipt', compact('invoice'));
    }

    /**
     * Get daily summary
     */
    public function dailySummary()
    {
        $today = today();

        $summary = [
            'total_sales' => SalesInvoice::whereDate('invoice_date', $today)->where('source', 'pos')->sum('total'),
            'sales_count' => SalesInvoice::whereDate('invoice_date', $today)->where('source', 'pos')->count(),
            'cash_sales' => SalesInvoice::whereDate('invoice_date', $today)->where('source', 'pos')->where('payment_method', 'cash')->sum('total'),
            'card_sales' => SalesInvoice::whereDate('invoice_date', $today)->where('source', 'pos')->where('payment_method', 'card')->sum('total'),
            'credit_sales' => SalesInvoice::whereDate('invoice_date', $today)->where('source', 'pos')->where('payment_method', 'credit')->sum('total'),
        ];

        return response()->json($summary);
    }

    /**
     * Hold/park a sale for later
     */
    public function holdSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
        ]);

        // Store in session for now (can be enhanced to DB)
        $heldSales = session('held_sales', []);
        $heldSales[] = [
            'id' => uniqid('hold_'),
            'items' => $request->items,
            'customer_id' => $request->customer_id,
            'notes' => $request->notes,
            'created_at' => now()->toDateTimeString(),
        ];
        session(['held_sales' => $heldSales]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعليق الفاتورة',
            'held_count' => count($heldSales),
        ]);
    }

    /**
     * Get held sales
     */
    public function getHeldSales()
    {
        return response()->json(session('held_sales', []));
    }

    /**
     * Resume a held sale
     */
    public function resumeSale(Request $request)
    {
        $holdId = $request->get('hold_id');
        $heldSales = session('held_sales', []);

        $sale = collect($heldSales)->firstWhere('id', $holdId);

        if (!$sale) {
            return response()->json(['error' => 'الفاتورة غير موجودة'], 404);
        }

        // Remove from held
        $heldSales = collect($heldSales)->reject(fn($s) => $s['id'] === $holdId)->values()->all();
        session(['held_sales' => $heldSales]);

        return response()->json($sale);
    }

    /**
     * Open a new shift
     */
    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        // Check if already has open shift
        $existing = PosShift::getActiveShift();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'لديك وردية مفتوحة بالفعل'
            ], 400);
        }

        $shift = PosShift::openNewShift($request->opening_cash);

        return response()->json([
            'success' => true,
            'message' => 'تم فتح الوردية بنجاح',
            'shift' => $shift
        ]);
    }

    /**
     * Close current shift
     */
    public function closeShift(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = PosShift::getActiveShift();
        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد وردية مفتوحة'
            ], 400);
        }

        $shift->close($request->closing_cash, $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'تم إغلاق الوردية بنجاح',
            'shift' => $shift->fresh()
        ]);
    }

    /**
     * Get current shift status
     */
    public function shiftStatus()
    {
        $shift = PosShift::getActiveShift();

        return response()->json([
            'hasActiveShift' => $shift !== null,
            'shift' => $shift
        ]);
    }

    /**
     * Shift report
     */
    public function shiftReport(PosShift $shift)
    {
        $shift->load('user');
        return view('pos.shift-report', compact('shift'));
    }

    /**
     * Get recent transactions for POS panel (AJAX)
     */
    public function recentTransactions()
    {
        $transactions = SalesInvoice::where('invoice_number', 'like', 'POS-%')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'invoice_number', 'total', 'created_at'])
            ->map(fn($t) => [
                'id' => $t->id,
                'invoice_number' => $t->invoice_number,
                'total' => $t->total,
                'created_at' => $t->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    /**
     * Get shift report data for quick view (AJAX)
     */
    public function shiftReportQuick()
    {
        $shift = PosShift::getActiveShift();

        if (!$shift) {
            // Return today's totals if no active shift
            $todayInvoices = SalesInvoice::where('invoice_number', 'like', 'POS-%')
                ->whereDate('created_at', today())
                ->get();

            return response()->json([
                'invoices_count' => $todayInvoices->count(),
                'total_sales' => $todayInvoices->sum('total'),
                'cash_total' => $todayInvoices->sum('paid_amount'), // Simplified
                'card_total' => 0,
            ]);
        }

        // Get invoices from current shift
        $invoices = SalesInvoice::where('invoice_number', 'like', 'POS-%')
            ->where('created_at', '>=', $shift->opened_at)
            ->get();

        return response()->json([
            'invoices_count' => $invoices->count(),
            'total_sales' => $invoices->sum('total'),
            'cash_total' => $shift->cash_sales ?? $invoices->sum('paid_amount'),
            'card_total' => $shift->card_sales ?? 0,
        ]);
    }
}

