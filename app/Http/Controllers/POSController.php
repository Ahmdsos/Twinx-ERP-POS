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

/**
 * POSController
 * Full Point of Sale system with touch-friendly interface
 */
class POSController extends Controller
{
    /**
     * POS main interface
     */
    public function index()
    {
        $categories = Category::active()->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get(['id', 'code', 'name']);
        $activeShift = PosShift::getActiveShift();

        return view('pos.index', compact('categories', 'customers', 'activeShift'));
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

            // Create invoice
            $invoice = SalesInvoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id,
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
            ]);

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

                // Reduce stock
                $this->reduceStock($product, $item['quantity'], $invoice);
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
     */
    protected function reduceStock(Product $product, float $quantity, SalesInvoice $invoice): void
    {
        // Find stock in default warehouse first, then any
        $stock = ProductStock::where('product_id', $product->id)
            ->where('quantity', '>', 0)
            ->orderByDesc('quantity')
            ->first();

        if ($stock) {
            $stock->decrement('quantity', $quantity);

            // Record movement
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_type' => 'out',
                'quantity' => $quantity,
                'unit_cost' => $product->cost_price,
                'reference_type' => SalesInvoice::class,
                'reference_id' => $invoice->id,
                'movement_date' => now(),
                'notes' => 'POS Sale: ' . $invoice->invoice_number,
            ]);
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
}

