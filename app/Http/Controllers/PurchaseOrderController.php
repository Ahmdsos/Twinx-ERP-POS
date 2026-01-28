<?php

namespace App\Http\Controllers;

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

        // Create PO header
        $po = new PurchaseOrder();
        $po->generateDocumentNumber();
        $po->supplier_id = $validated['supplier_id'];
        $po->warehouse_id = $validated['warehouse_id'];
        $po->order_date = $validated['order_date'];
        $po->expected_date = $validated['expected_date'] ?? null;
        $po->reference = $validated['reference'] ?? null;
        $po->notes = $validated['notes'] ?? null;
        $po->terms = $validated['terms'] ?? null;
        $po->status = PurchaseOrderStatus::DRAFT;
        $po->save();

        // Create lines
        $this->createLines($po, $validated['lines']);

        // Calculate totals
        $po->recalculateTotals();

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
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update header
        $purchaseOrder->update([
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'order_date' => $validated['order_date'],
            'expected_date' => $validated['expected_date'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
        ]);

        // Delete old lines and recreate
        $purchaseOrder->lines()->delete();
        $this->createLines($purchaseOrder, $validated['lines']);
        $purchaseOrder->recalculateTotals();

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

    /**
     * Helper to create PO lines
     */
    private function createLines(PurchaseOrder $po, array $lines): void
    {
        foreach ($lines as $line) {
            $product = Product::find($line['product_id']);
            $quantity = (float) $line['quantity'];
            $unitPrice = (float) $line['unit_price'];
            $discountPercent = (float) ($line['discount_percent'] ?? 0);

            $lineSubtotal = $quantity * $unitPrice;
            $discountAmount = $lineSubtotal * ($discountPercent / 100);
            $lineTotal = $lineSubtotal - $discountAmount;

            // Calculate tax if product has tax rate
            $taxRate = $product->tax_rate ?? 0;
            $taxAmount = $lineTotal * ($taxRate / 100);

            PurchaseOrderLine::create([
                'purchase_order_id' => $po->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal + $taxAmount,
            ]);
        }
    }
}
