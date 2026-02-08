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
    protected \Modules\Finance\Services\ExpenseService $expenseService;

    public function __construct(
        JournalService $journalService,
        InventoryService $inventoryService,
        POSService $posService,
        \Modules\Finance\Services\ExpenseService $expenseService
    ) {
        $this->journalService = $journalService;
        $this->inventoryService = $inventoryService;
        $this->posService = $posService;
        $this->expenseService = $expenseService;
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
        $drivers = DeliveryDriver::with('employee')
            ->where('status', 'available')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->employee ? $driver->employee->full_name : 'Driver #' . $driver->id
                ];
            })
            ->sortBy('name')
            ->values();

        // Phase 3: Cashier Selection for Managers
        $cashiers = \App\Models\User::active()->orderBy('name')->get(['id', 'name']);

        // Get dynamic tax rate from settings
        // Setting stores percentage (e.g., 20 for 20%), convert to decimal (0.20) for calculations
        $taxRatePercent = (float) Setting::getValue('default_tax_rate', 14);
        $taxRate = $taxRatePercent / 100;

        // Check if prices are tax-inclusive (tax already included in price)
        $taxInclusive = (bool) Setting::getValue('tax_inclusive', false);

        return view('pos.index', compact('categories', 'customers', 'warehouses', 'paymentAccounts', 'activeShift', 'drivers', 'cashiers', 'taxRate', 'taxRatePercent', 'taxInclusive'));
    }

    /**
     * Get shift status (Dashboard helper)
     */
    public function shiftStatus()
    {
        $shift = PosShift::getActiveShift();
        return response()->json([
            'success' => true,
            'is_open' => !!$shift,
            'shift' => $shift
        ]);
    }

    /**
     * Show detailed shift report (Z-Report)
     */
    public function shiftReport(PosShift $shift)
    {
        $shift->load(['user', 'invoices.customer', 'expenses', 'returns']);

        // Stats are already in the model via incrementSales, but for absolute truth we recalculate
        $sales = $shift->invoices;
        $expenses = $shift->expenses;
        $returns = $shift->returns;

        $stats = [
            'opening_cash' => (float) $shift->opening_cash,
            'total_sales' => (float) $shift->total_amount, // Use counter
            'total_cash' => (float) $shift->total_cash,   // Use counter
            'total_card' => (float) $shift->total_card,   // Use counter
            'total_credit' => (float) $shift->total_credit, // Use counter
            'total_expenses' => (float) $expenses->sum('amount'), // Expenses are joined, so sum is fine
            'total_returns' => (float) $returns->sum('total_amount'), // Returns are joined
            'expected_cash' => (float) ($shift->opening_cash + $shift->total_cash - $expenses->sum('amount') - $returns->sum('total_amount')),
            'closing_cash' => (float) $shift->closing_cash,
            'difference' => (float) $shift->cash_difference,
        ];

        return view('pos.reports.shift', compact('shift', 'stats'));
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
                'tax_rate' => $p->tax_rate !== null ? (float) $p->tax_rate : null,
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

        // H-04 FIX: Group barcode/SKU conditions to prevent bypassing is_active filter
        $product = Product::where(function ($q) use ($barcode) {
            $q->where('barcode', $barcode)
                ->orWhere('sku', $barcode);
        })
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
            'tax_rate' => $product->tax_rate !== null ? (float) $product->tax_rate : null,
            'stock' => $product->getTotalStock(),
            'unit' => $product->unit?->abbreviation ?? 'PCS',
            'image' => $product->primaryImageUrl,
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q', '');

        $customers = \Modules\Sales\Models\Customer::active()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('mobile', 'LIKE', "%{$query}%")
                        ->orWhere('code', 'LIKE', "%{$query}%");
                });
            })
            ->limit(10)
            ->get(['id', 'name', 'mobile', 'phone', 'code', 'balance', 'credit_limit', 'shipping_address', 'billing_address', 'shipping_city', 'billing_city']);

        $customers->transform(function ($customer) {
            $customer->address = $customer->shipping_address ?? $customer->billing_address ?? '';
            // Format full address if city exists
            if ($customer->address && ($customer->shipping_city || $customer->billing_city)) {
                $city = $customer->shipping_city ?? $customer->billing_city;
                $customer->address .= ' - ' . $city;
            }
            return $customer;
        });

        return response()->json($customers);
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
            // Fix: Enforce address for delivery
            'shipping_address' => 'required_if:is_delivery,true|nullable|string|max:500',
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

        if ($invoice->is_delivery) {
            return view('pos.receipt_delivery', compact('invoice'));
        }

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
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'subtotal' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        // Use DB persistence instead of session (C-04 fix)
        $heldSale = DB::transaction(function () use ($request) {
            return \App\Models\PosHeldSale::hold([
                'items' => $request->items,
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'subtotal' => $request->subtotal ?? 0,
                'tax' => $request->tax ?? 0,
                'total' => $request->total ?? 0,
                'notes' => $request->notes,
            ]);
        });

        $heldCount = \App\Models\PosHeldSale::held()->count();

        return response()->json([
            'success' => true,
            'message' => 'تم تعليق الفاتورة: ' . $heldSale->hold_number,
            'held_sale' => $heldSale,
            'held_count' => $heldCount,
        ]);
    }

    /**
     * Get held sales (from DB instead of session - C-04 fix)
     */
    public function getHeldSales()
    {
        $heldSales = \App\Models\PosHeldSale::held()
            ->with('customer:id,name')
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($heldSales);
    }

    /**
     * Resume a held sale (from DB instead of session - C-04 fix)
     */
    public function resumeSale(Request $request)
    {
        $holdId = $request->get('hold_id');

        // Find by hold_number or id
        $sale = \App\Models\PosHeldSale::held()
            ->where(function ($q) use ($holdId) {
                $q->where('id', $holdId)
                    ->orWhere('hold_number', $holdId);
            })
            ->first();

        if (!$sale) {
            return response()->json(['error' => 'الفاتورة غير موجودة'], 404);
        }

        // Mark as resumed in DB (keeps history)
        $sale->resume();

        return response()->json([
            'success' => true,
            'items' => $sale->items,
            'customer_id' => $sale->customer_id,
            'warehouse_id' => $sale->warehouse_id,
            'notes' => $sale->notes,
            'subtotal' => $sale->subtotal,
            'tax' => $sale->tax,
            'total' => $sale->total,
        ]);
    }

    /**
     * Open a new shift
     */
    public function openShift(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
        ]);

        // HR Integration: Ensure User is linked to an Active Employee
        // This enforces "Single Source of Truth" for who is operating the register
        $user = auth()->user();
        if (!$user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'عفواً، حسابك غير مرتبط بملف موظف. يرجى مراجعة الموارد البشرية.'
            ], 403);
        }

        // We use string 'active' here safely or upgrade to Enum if imported. 
        // Since we are in app codebase, let's use the Enum safely.
        if ($user->employee->status instanceof \Modules\HR\Enums\EmployeeStatus) {
            if ($user->employee->status !== \Modules\HR\Enums\EmployeeStatus::ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'عفواً، ملف الموظف الخاص بك غير نشط.'
                ], 403);
            }
        } elseif ($user->employee->status !== 'active') { // Fallback for string legacy
            return response()->json([
                'success' => false,
                'message' => 'عفواً، ملف الموظف الخاص بك غير نشط.'
            ], 403);
        }

        // Fix: Don't block if shift exists - let model handle auto-close (System Reality Check)
        // This prevents users from getting "stuck" if a shift wasn't closed properly
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
            'pin' => 'required|string',
        ]);

        // Phase 3: Reliability - Digital Signature (PIN)
        $closePin = \App\Models\Setting::getValue('pos_close_pin', '1234');
        if ($request->pin !== $closePin) {
            return response()->json(['success' => false, 'message' => 'رمز الإغلاق غير صحيح'], 403);
        }

        $shift = PosShift::getActiveShift();
        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'لا توجد وردية مفتوحة'], 400);
        }

        $shift->close((float) $request->closing_cash, $request->notes);

        return response()->json([
            'success' => true,
            'diff' => (float) $shift->cash_difference,
            'message' => 'تم إغلاق الوردية بنجاح',
            'report_url' => route('pos.shift.report', $shift->id)
        ]);
    }



    /**
     * Search for an invoice by number (for returns)
     */
    public function searchInvoice(Request $request)
    {
        $request->validate(['q' => 'required|string']);

        $invoice = SalesInvoice::where('invoice_number', $request->q)
            ->with(['lines.product', 'returns.lines'])
            ->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'الفاتورة غير موجودة'], 404);
        }

        // Calculate returned quantity for each line and prepare for JSON
        $lines = $invoice->lines->map(function ($line) use ($invoice) {
            // Logic unified with POSService for 100% truth synchronization
            $returned = \Modules\Sales\Models\SalesReturnLine::whereHas('salesReturn', function ($q) use ($invoice) {
                $q->where('sales_invoice_id', $invoice->id);
            })->where('product_id', $line->product_id)->sum('quantity');

            $lineData = $line->toArray();
            $lineData['product'] = $line->product; // Keep product info
            $lineData['returned_quantity'] = (float) $returned;
            $lineData['remaining_quantity'] = (float) max(0, (float) $line->quantity - (float) $returned);

            return $lineData;
        });

        // Reconstruct basic invoice data to avoid recursion or missing fields
        $invoiceData = $invoice->toArray();
        $invoiceData['lines'] = $lines;

        return response()->json([
            'success' => true,
            'invoice' => $invoiceData
        ]);
    }

    /**
     * Validate Refund PIN
     */
    public function validateRefundPin(Request $request)
    {
        $request->validate(['pin' => 'required|string']);

        // SECURITY FIX: Do NOT use default PIN - require explicit configuration
        $storedPin = Setting::getValue('pos_refund_pin');

        if (!$storedPin) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم تعيين الرقم السري للمرتجعات. يرجى التواصل مع المدير.'
            ], 500);
        }

        if ($request->pin === $storedPin) {
            return response()->json(['success' => true]);
        }

        // Log failed PIN attempt
        \App\Models\SecurityAuditLog::logFailedPin(auth()->id(), 'refund_pin');

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

        // SECURITY FIX: Do NOT use default PIN - require explicit configuration
        $storedPin = Setting::getValue('pos_refund_pin');
        if (!$storedPin) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم تعيين الرقم السري للمرتجعات. يرجى التواصل مع المدير.'
            ], 500);
        }
        if ($data['pin'] !== $storedPin) {
            \App\Models\SecurityAuditLog::logFailedPin(auth()->id(), 'sales_return');
            return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 403);
        }

        try {
            $salesReturn = $this->posService->salesReturn($data);

            // Log successful refund
            \App\Models\SecurityAuditLog::logRefund(
                $data['invoice_id'],
                $salesReturn->total_amount,
                null,
                $data['reason'] ?? null
            );

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

    /**
     * Validate Price Override PIN
     */
    public function validatePriceOverridePin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
            'product_id' => 'required|integer',
            'original_price' => 'required|numeric',
            'override_price' => 'required|numeric',
            'reason' => 'nullable|string|max:500'
        ]);

        // Get manager PIN for price overrides (can be same as refund or separate)
        $storedPin = Setting::getValue('pos_manager_pin', Setting::getValue('pos_refund_pin'));

        if (!$storedPin) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم تعيين الرقم السري للمدير. يرجى التواصل مع الإدارة.'
            ], 500);
        }

        // Check override limits
        $maxDiscountPercent = (float) Setting::getValue('pos_max_discount_percent', 50);
        $originalPrice = (float) $request->original_price;
        $overridePrice = (float) $request->override_price;

        if ($originalPrice > 0) {
            $discountPercent = (($originalPrice - $overridePrice) / $originalPrice) * 100;
            if ($discountPercent > $maxDiscountPercent) {
                return response()->json([
                    'success' => false,
                    'message' => "لا يمكن تخفيض السعر أكثر من {$maxDiscountPercent}%"
                ], 403);
            }
        }

        // Get active shift
        $activeShift = \App\Models\PosShift::getActiveShift();

        if ($request->pin === $storedPin) {
            // Log successful override
            \App\Models\PriceOverrideLog::logOverride([
                'shift_id' => $activeShift?->id,
                'product_id' => $request->product_id,
                'original_price' => $originalPrice,
                'override_price' => $overridePrice,
                'reason' => $request->reason,
                'approval_method' => 'pin',
                'is_approved' => true,
            ]);

            return response()->json(['success' => true, 'message' => 'تم اعتماد التعديل']);
        }

        // Log failed attempt
        \App\Models\SecurityAuditLog::logFailedPin(auth()->id(), 'price_override');

        \App\Models\PriceOverrideLog::logOverride([
            'shift_id' => $activeShift?->id,
            'product_id' => $request->product_id,
            'original_price' => $originalPrice,
            'override_price' => $overridePrice,
            'reason' => $request->reason,
            'approval_method' => 'pin',
            'is_approved' => false,
        ]);

        return response()->json(['success' => false, 'message' => 'الرقم السري غير صحيح'], 403);
    }

    /**
     * Log cart item deletion
     */
    public function logCartDeletion(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'product_name' => 'required|string',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'reason' => 'nullable|string|max:255'
        ]);

        \App\Models\SecurityAuditLog::logCartDelete(
            $request->product_id,
            $request->product_name,
            $request->quantity,
            $request->price,
            $request->reason
        );

        return response()->json(['success' => true]);
    }

    /**
     * X-Report (Shift summary without closing)
     */
    public function xReport()
    {
        $activeShift = \App\Models\PosShift::getActiveShift();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد وردية مفتوحة'
            ], 404);
        }

        // Get expenses and returns for the current shift
        $expenses = \Modules\Finance\Models\Expense::where('pos_shift_id', $activeShift->id)->get();
        // Returns logic: We need to know which returns affected CASH.
        // For now, assume all returns are CASH (deducted from drawer).
        // TODO: Future enhancement to track return payment method.
        $returns = \Modules\Sales\Models\SalesReturn::where('shift_id', $activeShift->id)->get();

        $totalExpenses = $expenses->sum('amount');
        $totalReturns = $returns->sum('total_amount');

        // Calculate Expected Cash in Drawer
        // Open + Sales(Cash) - Expenses - Returns
        // Note: We use total_cash from shift counters which handles split payments correctly.
        $expectedCash = (float) round($activeShift->opening_cash + $activeShift->total_cash - $totalExpenses - $totalReturns, 2);

        $stats = [
            'shift_id' => $activeShift->id,
            'started_at' => $activeShift->opened_at->format('Y-m-d H:i'),
            'duration' => $activeShift->opened_at->diffForHumans(null, true),
            'opening_cash' => (float) $activeShift->opening_cash,

            // Sales Totals (From Shift Counters - Truth Source)
            'total_sales' => (float) $activeShift->total_amount, // Revenue
            'transaction_count' => (int) $activeShift->total_sales, // Count

            // Payment Breakdown
            'total_cash' => (float) $activeShift->total_cash,
            'total_card' => (float) $activeShift->total_card,
            'total_credit' => (float) $activeShift->total_credit,

            'items_sold' => \Modules\Sales\Models\SalesInvoice::where('pos_shift_id', $activeShift->id)
                ->join('sales_invoice_lines', 'sales_invoices.id', '=', 'sales_invoice_lines.sales_invoice_id')
                ->sum('sales_invoice_lines.quantity'),

            // Outflows
            'total_expenses' => (float) $totalExpenses,
            'expense_count' => $expenses->count(),
            'total_returns' => (float) $totalReturns,
            'returns_count' => $returns->count(),

            'expected_cash' => $expectedCash,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Store Expense from POS (Simplified API)
     */
    public function saveExpense(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1',
            'notes' => 'required|string|max:255',
        ]);

        $activeShift = \App\Models\PosShift::getActiveShift();
        if (!$activeShift) {
            return response()->json(['success' => false, 'message' => 'no_shift'], 400);
        }

        try {
            // Use default Expense Category
            $category = \Modules\Finance\Models\ExpenseCategory::firstOrCreate(
                ['name' => 'نثريات تشغيل'],
                ['code' => 'EXP-GEN', 'is_active' => true]
            );

            // Record Expense via Service (Handles GL Posting)
            $expense = $this->expenseService->recordExpense([
                'expense_date' => now(),
                'category_id' => $category->id,
                'payment_account_id' => 1, // Cash account
                'amount' => $request->amount,
                'total_amount' => $request->amount,
                'payee' => 'POS Ops',
                'notes' => $request->notes . ' (Shift #' . $activeShift->id . ')',
                'status' => 'approved',
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'pos_shift_id' => $activeShift->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Expense saved and posted to GL',
                'balance' => $activeShift->refresh()->expected_cash
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("POS Expense Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get last transactions for current shift
     */
    public function lastTransactions(Request $request)
    {
        $limit = $request->get('limit', 10);

        $activeShift = \App\Models\PosShift::getActiveShift();

        $query = \Modules\Sales\Models\SalesInvoice::with(['customer', 'lines.product'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($activeShift) {
            $query->where('pos_shift_id', $activeShift->id);
        }

        $transactions = $query->get()->map(fn($inv) => [
            'id' => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'customer_name' => $inv->customer?->name ?? 'عابر',
            'total' => $inv->total,
            'payment_method' => $inv->payment_method,
            'items_count' => $inv->lines->count(),
            'lines' => $inv->lines->map(function ($l) use ($inv) {
                $returned = \Modules\Sales\Models\SalesReturnLine::whereHas('salesReturn', function ($q) use ($inv) {
                    $q->where('sales_invoice_id', $inv->id);
                })->where('product_id', $l->product_id)->sum('quantity');

                return [
                    'id' => $l->id,
                    'product_name' => $l->product?->name ?? 'Unknown',
                    'quantity' => (float) $l->quantity,
                    'unit_price' => (float) $l->unit_price,
                    'returned_quantity' => (float) $returned,
                    'remaining_quantity' => (float) max(0, (float) $l->quantity - (float) $returned),
                ];
            }),
            'created_at' => $inv->created_at->format('H:i'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Open cash drawer (log event)
     */
    public function openCashDrawer(Request $request)
    {
        $request->validate(['reason' => 'nullable|string|max:255']);

        \App\Models\SecurityAuditLog::log(
            \App\Models\SecurityAuditLog::EVENT_DRAWER_OPEN,
            'فتح درج النقدية يدوياً',
            ['reason' => $request->reason],
            \App\Models\SecurityAuditLog::SEVERITY_INFO
        );

        // Here you would send command to physical drawer if connected
        // For now, just log and return success
        return response()->json([
            'success' => true,
            'message' => 'تم فتح درج النقدية'
        ]);
    }



    // --- PHASE 3: Delivery Management ---

    public function listDeliveryOrders()
    {
        $warehouseId = auth()->user()->warehouse_id ?? 1; // Filter by warehouse

        $orders = \Modules\Sales\Models\DeliveryOrder::with(['salesInvoice', 'customer', 'driver'])
            ->where('warehouse_id', $warehouseId)
            ->whereNotIn('status', [\Modules\Sales\Enums\DeliveryStatus::DELIVERED, \Modules\Sales\Enums\DeliveryStatus::RETURNED, \Modules\Sales\Enums\DeliveryStatus::CANCELLED])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'invoice_number' => $order->salesInvoice->invoice_number,
                    'customer_name' => $order->customer->name,
                    'address' => $order->shipping_address,
                    'driver_name' => $order->driver ? $order->driver->name : 'Unassigned',
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                    'total' => $order->salesInvoice->total,
                    'created_at' => $order->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function updateDeliveryStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:delivery_orders,id',
            'status' => 'required|string|in:ready,shipped,delivered,returned,cancelled' // Match Enum values
        ]);

        $order = \Modules\Sales\Models\DeliveryOrder::findOrFail($request->id);

        $statusEnum = \Modules\Sales\Enums\DeliveryStatus::tryFrom($request->status);

        if (!$statusEnum) {
            return response()->json(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $order->update(['status' => $statusEnum]);

        // Release driver if task is done or cancelled
        if (in_array($statusEnum->value, ['delivered', 'cancelled', 'returned'])) {
            $order->driver?->release();
        }

        return response()->json(['success' => true, 'message' => 'Delivery status updated']);
    }

    /**
     * Store Expense (Petty Cash)
     */
    public function storeExpense(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
        ]);

        $expense = \Modules\Finance\Models\Expense::create([
            'expense_date' => now(),
            'amount' => $request->amount,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
            'pos_shift_id' => \App\Models\PosShift::getActiveShift()?->id,
            'category_id' => $request->category_id,
            'total_amount' => $request->amount, // Finance model requires total_amount
            'status' => 'approved',
        ]);

        return response()->json(['success' => true, 'message' => 'تم تسجيل المصروف بنجاح', 'expense' => $expense]);
    }
}

