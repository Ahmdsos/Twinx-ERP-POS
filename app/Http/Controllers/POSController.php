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
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Services\InventoryService;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Accounting\Models\Account;
use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Services\JournalService;

/**
 * POSController
 * Full Point of Sale system with touch-friendly interface
 */
class POSController extends Controller
{
    protected JournalService $journalService;
    protected InventoryService $inventoryService;

    public function __construct(JournalService $journalService, InventoryService $inventoryService)
    {
        $this->journalService = $journalService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * POS main interface
     */
    public function index()
    {
        $categories = Category::with([
            'products' => function ($q) {
                $q->where('is_active', true);
            }
        ])->active()->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get(['id', 'code', 'name', 'type']);
        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name']);
        $paymentAccounts = Account::whereIn('code', ['1100', '1101', '1102', '1110', '1111'])
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
        $activeShift = PosShift::getActiveShift();

        return view('pos.index', compact('categories', 'customers', 'warehouses', 'paymentAccounts', 'activeShift'));
    }

    /**
     * Search products (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id'); // Get selected warehouse

        $products = Product::query()
            ->where('is_active', true)
            ->where('is_sellable', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->with([
                'category:id,name',
                'unit:id,name,abbreviation',
                'images',
                'stock' => function ($q) use ($warehouseId) {
                    if ($warehouseId) {
                        $q->where('warehouse_id', $warehouseId);
                    }
                }
            ])
            ->when($warehouseId, function ($q) use ($warehouseId) {
                // Filter by stock only if "Allow Negative Stock" is FALSE
                $allowNegative = \App\Models\Setting::getValue('pos_allow_negative_stock', false);

                if (!$allowNegative) {
                    $q->whereHas('stock', function ($sq) use ($warehouseId) {
                        $sq->where('warehouse_id', $warehouseId)
                            ->where('quantity', '>', 0);
                    });
                }
            })
            ->limit(50)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'name' => $p->name,
                'price' => (float) $p->selling_price,
                // Multi-tier prices
                'price_distributor' => (float) $p->price_distributor,
                'price_wholesale' => (float) $p->price_wholesale,
                'price_half_wholesale' => (float) $p->price_half_wholesale,
                'price_quarter_wholesale' => (float) $p->price_quarter_wholesale,
                'price_special' => (float) $p->price_special,

                'cost' => (float) $p->cost_price,
                'tax_rate' => (float) $p->tax_rate,
                // Get stock for specific warehouse, or total if no warehouse selected (though UI should enforce one)
                'stock' => $warehouseId
                    ? (float) ($p->stock->first()?->available_quantity ?? 0)
                    : (float) $p->available_stock,
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
            'items.*.quantity' => 'required|numeric|not_in:0', // Allow negative for returns
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required|in:cash,card,credit,split',
            'amount_paid' => 'required|numeric', // Allow negative for refunds
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get or create Walk-in Customer for POS
        $customerId = $request->customer_id;
        if (!$customerId) {
            $walkInCustomer = Customer::firstOrCreate(
                ['code' => 'WALK-IN'],
                [
                    'name' => 'Walk-in Customer',
                    'phone' => 'N/A',
                    'is_active' => true,
                    'created_by' => 1,
                ]
            );
            $customerId = $walkInCustomer->id;
        }

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $invoiceLinesData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Calculate line values
                $quantity = $item['quantity'];
                $price = $item['price'];
                $discount = $item['discount'] ?? 0;

                $lineTotalBeforeTax = ($quantity * $price) - $discount;

                // Calculate Tax
                // 1. Determine Rate (Product specific takes precedence, otherwise Global)
                // If product tax is NULL, use Global. If Product Tax is 0, use 0.
                $globalTax = \App\Models\Setting::getValue('default_tax_rate', 0);
                $taxRate = $product->tax_rate ?? $globalTax;

                // 2. Check Inclusive/Exclusive Setting
                $isInclusive = \App\Models\Setting::getValue('tax_inclusive', false);

                if ($isInclusive) {
                    // Price is Gross
                    $lineGross = ($quantity * $price) - $discount;

                    // Net = Gross / (1 + Rate/100)
                    $lineNet = $lineGross / (1 + ($taxRate / 100));
                    $taxAmount = $lineGross - $lineNet;

                    $lineTotal = $lineGross;
                } else {
                    // Price is Net
                    $lineNet = ($quantity * $price) - $discount;

                    // Tax = Net * Rate/100
                    $taxAmount = ($lineNet * $taxRate) / 100;

                    $lineTotal = $lineNet + $taxAmount;
                }

                $subtotal += $lineNet;
                $totalTax += $taxAmount;

                // Prepare line data for creation later
                $invoiceLinesData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $isInclusive ? ($price / (1 + ($taxRate / 100))) : $price, // Always store Net Unit Price for consistency? Or maintain user input? Let's store Net to be safe for accounting.
                    'discount_amount' => $discount,
                    'tax_percent' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal, // This is Gross Line Total
                    'product_instance' => $product
                ];
            }

            $discount = $request->get('discount', 0);

            // Total = Net Subtotal + Total Tax - Global Discount
            $total = $subtotal + $totalTax - $discount;

            // Recalculate Change/Due
            $amountPaid = $request->amount_paid;
            $change = max(0, $amountPaid - $total);
            $balanceDue = max(0, $total - $amountPaid);

            // Generate invoice number
            $datestamp = now()->format('Ymd');
            $prefix = 'POS-' . $datestamp . '-';

            // Find last POS invoice specifically to avoid collision with 'INV-' or other types
            $lastPosInvoice = SalesInvoice::where('invoice_number', 'like', $prefix . '%')
                ->orderByDesc('id')
                ->first();

            $sequence = $lastPosInvoice ? (int) substr($lastPosInvoice->invoice_number, -4) + 1 : 1;

            // Double check availability (simple collision avoidance)
            $invoiceNumber = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            while (SalesInvoice::where('invoice_number', $invoiceNumber)->exists()) {
                $sequence++;
                $invoiceNumber = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            }

            // Create invoice
            $invoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customerId, // Use walk-in customer if not specified
                'invoice_date' => now(),
                'due_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $totalTax,
                'total' => $total,
                'paid_amount' => $amountPaid, // Store actual paid amount (even if > total)
                'balance_due' => $balanceDue,
                'status' => $balanceDue > 0 ? \Modules\Sales\Enums\SalesInvoiceStatus::PARTIAL : \Modules\Sales\Enums\SalesInvoiceStatus::PAID,
                'notes' => $request->notes,
            ]);

            // Create invoice lines and update stock
            foreach ($invoiceLinesData as $lineData) {
                // Keep product instance for stock reduction
                $product = $lineData['product_instance'];
                unset($lineData['product_instance']);

                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    ...$lineData
                ]);

                // Stock Management (Sale vs Return)
                // Simplify: Just reduce stock and log movement. No COGS reduction here.
                if ($lineData['quantity'] > 0) {
                    $this->reduceStock($product, $lineData['quantity'], $invoice, $request->get('warehouse_id'));
                } else {
                    $this->restoreStock($product->id, abs($lineData['quantity']), $invoice->invoice_number);
                }
            }

            // Record Payment if amount paid > 0
            if ($amountPaid > 0) {
                // Use SalesService to handle payment creation and allocation (SSOT)
                // Assuming SalesService is injected as $salesService
                $paymentMethod = $request->payment_method ?? 'cash';

                // Find default cash account if not provided
                $paymentAccountId = $request->payment_account_id;
                if (!$paymentAccountId) {
                    $paymentAccountId = \Modules\Accounting\Models\Account::where('code', 'like', '1%')->first()->id ?? 1;
                }

                // If SalesService expects specific args, align them.
                // receivePayment(Customer $customer, float $amount, string $method, ?string $reference, ?string $notes)
                // NOTE: We need to allocate this payment to the invoice we just created.

                $payment = \Modules\Sales\Models\CustomerPayment::create([
                    'customer_id' => $customerId,
                    'payment_account_id' => $paymentAccountId, // Required field
                    'payment_date' => now(), // Use full timestamp
                    'amount' => $amountPaid,
                    'payment_method' => $paymentMethod,
                    'reference' => 'POS-' . $invoiceNumber, // specific ref
                    'receipt_number' => 'RCT-' . now()->format('YmdHis') . '-' . rand(100, 999), // unique receipt
                    'notes' => 'POS Payment for ' . $invoiceNumber,
                    'created_by' => auth()->id(),
                ]);

                // Allocate to this invoice
                \Modules\Sales\Models\CustomerPaymentAllocation::create([
                    'customer_payment_id' => $payment->id,
                    'sales_invoice_id' => $invoice->id,
                    'amount' => min($amountPaid, $total), // Allocate up to invoice total
                ]);

                // Update Invoice Paid Amount (Redundant but safe for consistency if not using observers)
                // Actually the invoice was created with paid_amount, so we are good.
            }

            // Create Single Journal Entry (TEMP_SALE_ENTRY)
            $journalEntryId = $this->createJournalEntry($invoice, $request->payment_method);

            // Link JE to Invoice
            if ($journalEntryId) {
                $invoice->journal_entry_id = $journalEntryId;
                $invoice->save();
            }

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
     * Reduce stock for POS sale
     * REFACTORED: Uses InventoryService (Single Write Path Enforcement)
     */
    protected function reduceStock(Product $product, float $quantity, SalesInvoice $invoice, ?int $warehouseId = null): void
    {
        // Get warehouse (default to first if not specified)
        $warehouseId = $warehouseId ?? Warehouse::first()?->id ?? 1;
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Use InventoryService for proper stock reduction with event logging
        $this->inventoryService->removeStock(
            $product,
            $warehouse,
            $quantity,
            MovementType::SALE,
            $invoice->invoice_number,
            'POS Sale',
            SalesInvoice::class,
            $invoice->id
        );
    }

    /**
     * Create journal entry for POS sale
     * Simplified: Single Entry (Revenue only), tagged as TEMP_SALE_ENTRY
     */
    protected function createJournalEntry(SalesInvoice $invoice, string $paymentMethod): ?int
    {
        // Find accounts - using actual chart of accounts codes
        $cashAccount = Account::where('code', '1101')->first(); // Cash
        $bankAccount = Account::where('code', '1102')->first(); // Bank
        $salesAccount = Account::where('code', '4101')->first(); // Sales Revenue

        if (!$salesAccount)
            return null;

        $debitAccount = ($paymentMethod === 'card' || $paymentMethod === 'bank')
            ? ($bankAccount ?? $cashAccount)
            : $cashAccount;

        if (!$debitAccount)
            return null;

        $lines = [];

        // Debit: Cash/Bank
        $lines[] = [
            'account_id' => $debitAccount->id,
            'debit' => $invoice->total,
            'credit' => 0,
            'description' => 'Cash received (POS)'
        ];

        // Credit: Sales Revenue
        $lines[] = [
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => $invoice->total,
            'description' => 'Sales Revenue (POS)'
        ];

        try {
            $entry = $this->journalService->create([
                'entry_date' => now(),
                'reference' => $invoice->invoice_number,
                'description' => 'POS Sale: ' . $invoice->invoice_number . ' [TEMP_SALE_ENTRY]',
                'source_type' => SalesInvoice::class,
                'source_id' => $invoice->id,
            ], $lines);

            // Auto-post
            $this->journalService->post($entry);

            return $entry->id;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("POS Journal Entry Failed: " . $e->getMessage());
            throw $e; // Enforce atomic transaction
        }
    }

    /**
     * Process Sales Return from POS
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
            $returnNumber = 'RET-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create return invoice (negative amounts)
            $returnTotal = 0;
            $returnLines = [];

            foreach ($request->items as $item) {
                $originalLine = SalesInvoiceLine::find($item['line_id']);
                if (!$originalLine)
                    continue;

                $lineTotal = $originalLine->unit_price * $item['quantity'];
                $returnTotal += $lineTotal;

                $returnLines[] = [
                    'product_id' => $originalLine->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $originalLine->unit_price,
                    'line_total' => $lineTotal,
                ];

                // Restore stock
                $this->restoreStock($originalLine->product_id, $item['quantity'], $returnNumber);
            }

            // Create return record (could be separate table or negative invoice)
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
            ]);

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
     * Restore stock for returns
     * REFACTORED: Uses InventoryService (Single Write Path Enforcement)
     */
    protected function restoreStock(int $productId, float $quantity, string $reference): void
    {
        $product = Product::findOrFail($productId);

        // Get stock to determine warehouse and cost
        $stock = ProductStock::where('product_id', $productId)->first();
        $warehouseId = $stock?->warehouse_id ?? Warehouse::first()?->id ?? 1;
        $warehouse = Warehouse::findOrFail($warehouseId);
        $unitCost = $stock?->average_cost ?? $product->cost_price;

        // Use InventoryService for proper stock addition with event logging
        $this->inventoryService->addStock(
            $product,
            $warehouse,
            $quantity,
            $unitCost,
            MovementType::RETURN_IN,
            $reference,
            'Sales Return'
        );
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
            'closing_cash' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = PosShift::getActiveShift();
        if (!$shift) {
            return response()->json(['message' => 'لا يوجد وردية مفتوحة'], 400);
        }

        // Logic handled by model, assume it takes exact cash and calculates diff
        // If not, we implement here:
        $expectedCash = $shift->opening_cash + $shift->sales_cash; // Simplified
        $closingCash = $request->closing_cash ?? $expectedCash;
        $diff = $closingCash - $expectedCash;

        $shift->update([
            'closing_cash' => $closingCash,
            'difference' => $diff,
            'closed_at' => now(),
            'status' => 'closed',
            'notes' => $request->notes
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إغلاق الوردية بنجاح',
            'diff' => $diff
        ]);
    }

    public function shiftStatus()
    {
        $shift = PosShift::getActiveShift();
        return response()->json([
            'hasActiveShift' => $shift !== null,
            'shift' => $shift
        ]);
    }

    public function shiftReport(PosShift $shift)
    {
        // Placeholder view or JSON
        return response()->json($shift);
    }



    public function recentTransactions()
    {
        $invoices = SalesInvoice::latest()->limit(5)->get();
        return response()->json($invoices);
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
