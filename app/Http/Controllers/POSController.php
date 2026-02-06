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
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Models\DeliveryOrderLine;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\HR\Models\DeliveryDriver;
use Modules\Sales\Services\POSService;
use App\Models\Setting;

/**
 * POSController
 * Full Point of Sale system with touch-friendly interface
 */
class POSController extends Controller
{
    protected JournalService $journalService;
    protected InventoryService $inventoryService;
    protected POSService $posService;

    public function __construct(
        JournalService $journalService,
        InventoryService $inventoryService,
        POSService $posService
    ) {
        $this->journalService = $journalService;
        $this->inventoryService = $inventoryService;
        $this->posService = $posService;
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
        $drivers = DeliveryDriver::where('status', 'available')->orderBy('name')->get(['id', 'name']);

        return view('pos.index', compact('categories', 'customers', 'warehouses', 'paymentAccounts', 'activeShift', 'drivers'));
    }

    /**
     * Search products (AJAX)
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $categoryId = $request->get('category_id');
        $warehouseId = $request->get('warehouse_id'); // Get selected warehouse

        $products = Product::with(['category', 'unit', 'stocks'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('sku', 'LIKE', "%{$query}%")
                        ->orWhere('barcode', 'LIKE', "%{$query}%")
                        ->orWhere('description', 'LIKE', "%{$query}%");
                });
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->active()
            ->where('is_sellable', true) // Keep this explicit check
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

    public function quickCreateCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
        ]);

        $customer = \Modules\Sales\Models\Customer::create([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'phone' => $data['mobile'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => 'تم إضافة العميل بنجاح',
        ]);
    }

    /**
     * Get brief customer info for POS
     */
    public function getCustomerBrief(Request $request, $id)
    {
        $customer = \Modules\Sales\Models\Customer::findOrFail($id);

        $lastInvoice = $customer->salesInvoices()
            ->latest('invoice_date')
            ->first();

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'balance' => (float) $customer->balance,
            'credit_limit' => (float) $customer->credit_limit,
            'is_blocked' => (bool) $customer->is_blocked,
            'last_invoice_date' => $lastInvoice ? $lastInvoice->invoice_date->format('Y-m-d') : 'لا يوجد',
        ]);
    }

    /**
     * Process sale (checkout)
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|not_in:0',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,credit,bank',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.account_id' => 'nullable|exists:accounts,id',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'is_delivery' => 'nullable|boolean',
            'driver_id' => 'nullable|exists:hr_delivery_drivers,id',
            'delivery_fee' => 'nullable|numeric|min:0',
            'shipping_address' => 'nullable|string|max:500',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        try {
            $invoice = $this->posService->checkout($data);

            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'total' => $invoice->total,
                    'amount_paid' => $invoice->paid_amount,
                    'balance_due' => $invoice->balance_due,
                ],
                'message' => 'تم إتمام عملية البيع بنجاح',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("POS Checkout Failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
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
     * Get live shift statistics
     */
    public function getShiftStats()
    {
        $shift = PosShift::getActiveShift();
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'No active shift'], 404);
        }

        return response()->json([
            'success' => true,
            'shift' => [
                'id' => $shift->id,
                'opening_cash' => (float) $shift->opening_cash,
                'total_sales' => $shift->total_sales,
                'total_amount' => (float) $shift->total_amount,
                'total_cash' => (float) $shift->total_cash,
                'total_card' => (float) $shift->total_card,
                'expected_cash' => (float) ($shift->opening_cash + $shift->total_cash),
            ]
        ]);
    }

    /**
     * Close current shift with reconciliation
     */
    public function closeShift(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = PosShift::getActiveShift();
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'لا توجد وردية مفتوحة'], 400);
        }

        $shift->close((float) $request->closing_cash, $request->notes);

        return response()->json([
            'success' => true,
            'diff' => (float) $shift->cash_difference,
            'message' => 'تم إغلاق الوردية بنجاح'
        ]);
    }



    /**
     * Search for an invoice by number (for returns)
     */
    public function searchInvoice(Request $request)
    {
        $request->validate(['q' => 'required|string']);

        $invoice = SalesInvoice::where('invoice_number', $request->q)
            ->with('lines.product')
            ->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة'], 404);
        }

        return response()->json([
            'success' => true,
            'invoice' => $invoice
        ]);
    }

    /**
     * Validate Refund PIN
     */
    public function validateRefundPin(Request $request)
    {
        $request->validate(['pin' => 'required|string']);

        $storedPin = Setting::getValue('pos_refund_pin', '1234');

        if ($request->pin === $storedPin) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 403);
    }

    /**
     * Process sales return
     */
    public function salesReturn(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:sales_invoices,id',
            'items' => 'required|array',
            'items.*.line_id' => 'required|exists:sales_invoice_lines,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'pin' => 'required|string'
        ]);

        // Security Check
        $storedPin = Setting::getValue('pos_refund_pin', '1234');
        if ($data['pin'] !== $storedPin) {
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 403);
        }

        try {
            $salesReturn = $this->posService->salesReturn($data);
            return response()->json([
                'success' => true,
                'message' => 'تم إتمام المرتجع بنجاح',
                'return' => [
                    'id' => $salesReturn->id,
                    'number' => $salesReturn->return_number,
                    'total' => $salesReturn->total_amount,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



}
