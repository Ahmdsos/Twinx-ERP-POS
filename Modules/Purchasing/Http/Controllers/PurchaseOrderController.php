<?php

namespace Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Models\PurchaseOrderLine;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Enums\PurchaseOrderStatus;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;

/**
 * PurchaseOrderController - Manages Purchase Order UI operations
 * 
 * Handles:
 * - PO CRUD operations
 * - Approve/Cancel actions
 * - Dynamic product info for forms
 */
class PurchaseOrderController extends Controller
{
    protected $purchasingService;

    public function __construct(\Modules\Purchasing\Services\PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display list of purchase orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse'])
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Search by PO number
        if ($request->filled('search')) {
            $query->where('po_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date
        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->to_date);
        }

        $purchaseOrders = $query->paginate(20);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $statuses = PurchaseOrderStatus::cases();

        return view('purchasing.orders.index', compact('purchaseOrders', 'suppliers', 'statuses'));
    }

    /**
     * Show form for creating new purchase order
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::purchasable()->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('purchasing.orders.create', compact('suppliers', 'products', 'warehouses'));
    }

    /**
     * Store new purchase order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            // Line items
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $po = $this->purchasingService->createPurchaseOrder($validated, $validated['lines']);

        return redirect()->route('purchase-orders.show', $po)
            ->with('success', 'تم إنشاء أمر الشراء بنجاح: ' . $po->po_number);
    }

    /**
     * Display purchase order details
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'lines.product.unit',
            'grns',
            'invoices',
            'approver',
        ]);

        return view('purchasing.orders.show', compact('purchaseOrder'));
    }

    /**
     * Show edit form for purchase order
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canEdit()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'لا يمكن تعديل هذا الأمر - الحالة: ' . $purchaseOrder->status->label());
        }

        $purchaseOrder->load(['supplier', 'lines.product.unit']);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::purchasable()->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('purchasing.orders.edit', compact('purchaseOrder', 'suppliers', 'products', 'warehouses'));
    }

    /**
     * Update purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canEdit()) {
            return redirect()->route('purchase-orders.show', $purchaseOrder)
                ->with('error', 'لا يمكن تعديل هذا الأمر');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.id' => 'nullable|exists:purchase_order_lines,id',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $this->purchasingService->updatePurchaseOrder($purchaseOrder, $validated, $validated['lines']);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'تم تحديث أمر الشراء بنجاح');
    }

    /**
     * Approve purchase order
     */
    public function approve(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->approve()) {
            return back()->with('success', 'تم اعتماد أمر الشراء بنجاح');
        }

        return back()->with('error', 'لا يمكن اعتماد هذا الأمر');
    }

    /**
     * Cancel purchase order
     */
    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canCancel()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الأمر');
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::CANCELLED]);

        return back()->with('success', 'تم إلغاء أمر الشراء');
    }

    /**
     * Get product info via AJAX
     */
    public function getProductInfo(Request $request)
    {
        $product = Product::with('unit')->find($request->product_id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'cost_price' => $product->cost_price,
            'unit' => $product->unit?->name ?? '-',
        ]);
    }
}
