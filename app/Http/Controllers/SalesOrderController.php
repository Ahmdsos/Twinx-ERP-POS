<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Models\SalesOrderLine;
use Modules\Sales\Models\Customer;
use Modules\Sales\Enums\SalesOrderStatus;
use Modules\Sales\Services\SalesService;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;

/**
 * SalesOrderController - Manages Sales Order UI operations
 * 
 * Handles the complete SO workflow:
 * - Create/Edit orders (DRAFT)
 * - Confirm orders
 * - Cancel orders
 * - View order details & related documents
 */
class SalesOrderController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {
    }

    /**
     * Display list of sales orders with filters
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'warehouse'])
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->to_date);
        }

        // Search by SO number
        if ($request->filled('search')) {
            $query->where('so_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(20);
        $customers = Customer::orderBy('name')->get();
        $statuses = SalesOrderStatus::cases();

        return view('sales.orders.index', compact('orders', 'customers', 'statuses'));
    }

    /**
     * Show form for creating new sales order
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['unit', 'stock'])
            ->orderBy('name')
            ->get();

        // Tax settings from database (stored as percent, e.g., 20 for 20%)
        $taxRatePercent = (float) \App\Models\Setting::getValue('default_tax_rate', 14);
        $taxRate = $taxRatePercent / 100; // Convert to decimal for JS

        return view('sales.orders.create', compact('customers', 'warehouses', 'products', 'taxRate', 'taxRatePercent'));
    }

    /**
     * Store new sales order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            // Line items
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.notes' => 'nullable|string',
        ]);

        // Prepare order data
        $orderData = [
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'order_date' => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'customer_notes' => $validated['customer_notes'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'shipping_method' => $validated['shipping_method'] ?? null,
        ];

        // Prepare lines data
        $lines = collect($validated['lines'])->map(function ($line) {
            return [
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_percent' => $line['discount_percent'] ?? 0,
                'notes' => $line['notes'] ?? null,
            ];
        })->toArray();

        $order = $this->salesService->createSalesOrder($orderData, $lines);

        return redirect()->route('sales-orders.show', $order)
            ->with('success', 'تم إنشاء أمر البيع بنجاح: ' . $order->so_number);
    }

    /**
     * Display sales order details
     */
    public function show(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'customer',
            'warehouse',
            'lines.product.unit',
            'deliveryOrders.lines',
            'invoices',
        ]);

        return view('sales.orders.show', compact('salesOrder'));
    }

    /**
     * Show form for editing sales order (DRAFT only)
     */
    public function edit(SalesOrder $salesOrder)
    {
        if (!$salesOrder->canEdit()) {
            return back()->with('error', 'لا يمكن تعديل هذا الأمر - الحالة: ' . $salesOrder->status->label());
        }

        $salesOrder->load(['lines.product.unit']);

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with(['unit', 'stock'])
            ->orderBy('name')
            ->get();

        // Tax settings from database (stored as percent, e.g., 20 for 20%)
        $taxRatePercent = (float) \App\Models\Setting::getValue('default_tax_rate', 14);
        $taxRate = $taxRatePercent / 100; // Convert to decimal for JS

        return view('sales.orders.edit', compact('salesOrder', 'customers', 'warehouses', 'products', 'taxRate', 'taxRatePercent'));
    }

    /**
     * Update sales order (DRAFT only)
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        if (!$salesOrder->canEdit()) {
            return back()->with('error', 'لا يمكن تعديل هذا الأمر');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.notes' => 'nullable|string',
        ]);

        // Update order header
        $salesOrder->update([
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'order_date' => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'customer_notes' => $validated['customer_notes'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'shipping_method' => $validated['shipping_method'] ?? null,
        ]);

        // Delete old lines and create new ones
        $salesOrder->lines()->delete();

        foreach ($validated['lines'] as $lineData) {
            $product = Product::find($lineData['product_id']);
            $quantity = $lineData['quantity'];
            $unitPrice = $lineData['unit_price'];
            $discountPercent = $lineData['discount_percent'] ?? 0;
            $discountAmount = ($unitPrice * $quantity) * ($discountPercent / 100);
            $lineTotal = ($unitPrice * $quantity) - $discountAmount;

            $salesOrder->lines()->create([
                'product_id' => $lineData['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_percent' => 0, // TODO: Implement tax
                'tax_amount' => 0,
                'line_total' => $lineTotal,
                'notes' => $lineData['notes'] ?? null,
            ]);
        }

        // Recalculate totals
        $salesOrder->recalculateTotals();

        return redirect()->route('sales-orders.show', $salesOrder)
            ->with('success', 'تم تحديث أمر البيع بنجاح');
    }

    /**
     * Confirm a draft sales order
     */
    public function confirm(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== SalesOrderStatus::DRAFT) {
            return back()->with('error', 'يمكن تأكيد المسودات فقط');
        }

        $this->salesService->confirmSalesOrder($salesOrder);

        return back()->with('success', 'تم تأكيد أمر البيع بنجاح');
    }

    /**
     * Cancel a sales order
     */
    public function cancel(SalesOrder $salesOrder)
    {
        if (!$salesOrder->canCancel()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الأمر');
        }

        $salesOrder->update(['status' => SalesOrderStatus::CANCELLED]);

        return back()->with('success', 'تم إلغاء أمر البيع');
    }

    /**
     * Delete a draft sales order
     */
    public function destroy(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== SalesOrderStatus::DRAFT) {
            return back()->with('error', 'يمكن حذف المسودات فقط');
        }

        $soNumber = $salesOrder->so_number;
        $salesOrder->lines()->delete();
        $salesOrder->delete();

        return redirect()->route('sales-orders.index')
            ->with('success', 'تم حذف أمر البيع: ' . $soNumber);
    }

    public function print(SalesOrder $salesOrder)
    {
        return view('sales.orders.print', compact('salesOrder'));
    }

    public function deliver(SalesOrder $salesOrder)
    {
        return redirect()->route('deliveries.create', ['sales_order_id' => $salesOrder->id]);
    }

    public function invoice(SalesOrder $salesOrder)
    {
        // Placeholder for invoice logic
        // Ideally redirect to invoices.create with SO data
        return redirect()->route('sales-invoices.create', ['from_so' => $salesOrder->id]);
    }

    /**
     * Get product info for AJAX (price, stock)
     */
    public function getProductInfo(Request $request)
    {
        $product = Product::with([
            'unit',
            'stock' => function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            }
        ])->find($request->product_id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $stock = $product->stock->first();

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'selling_price' => $product->selling_price,
            'unit' => $product->unit?->abbreviation ?? '',
            'available_qty' => $stock ? ($stock->quantity - $stock->reserved_quantity) : 0,
        ]);
    }
}
