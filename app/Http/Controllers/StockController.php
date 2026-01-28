<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Enums\MovementType;

/**
 * StockController - Stock Management UI
 * 
 * Handles:
 * - Stock movements (receive, issue, adjust)
 * - Stock transfers between warehouses
 * - Stock movement history
 */
class StockController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {
    }

    /**
     * Display stock movements history
     */
    public function index(Request $request)
    {
        $query = StockMovement::with(['product', 'warehouse'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id');

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by movement type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('movement_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('movement_date', '<=', $request->to_date);
        }

        $movements = $query->paginate(25);
        $products = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $types = MovementType::cases();

        return view('inventory.stock.index', compact('movements', 'products', 'warehouses', 'types'));
    }

    /**
     * Show form for adding stock (receiving)
     */
    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock.create', compact('products', 'warehouses'));
    }

    /**
     * Store new stock (receive operation)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'required|numeric|min:0',
            'type' => 'required|in:purchase,adjustment_in,initial',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        // Map form type to MovementType enum
        $movementType = match ($validated['type']) {
            'purchase' => MovementType::PURCHASE,
            'adjustment_in' => MovementType::ADJUSTMENT_IN,
            'initial' => MovementType::INITIAL,
            default => MovementType::ADJUSTMENT_IN,
        };

        $this->inventoryService->addStock(
            product: $product,
            warehouse: $warehouse,
            quantity: $validated['quantity'],
            unitCost: $validated['unit_cost'],
            type: $movementType,
            reference: $validated['reference'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('stock.index')
            ->with('success', 'تم إضافة المخزون بنجاح');
    }

    /**
     * Show form for stock adjustment
     */
    public function adjust()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock.adjust', compact('products', 'warehouses'));
    }

    /**
     * Process stock adjustment
     */
    public function processAdjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'new_quantity' => 'required|numeric|min:0',
            'new_unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $this->inventoryService->adjust(
            product: $product,
            warehouse: $warehouse,
            newQuantity: $validated['new_quantity'],
            newUnitCost: $validated['new_unit_cost'] ?? null,
            reason: $validated['reason'] ?? null
        );

        return redirect()->route('stock.index')
            ->with('success', 'تم تسوية المخزون بنجاح');
    }

    /**
     * Show form for stock transfer
     */
    public function transfer()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock.transfer', compact('products', 'warehouses'));
    }

    /**
     * Process stock transfer
     */
    public function processTransfer(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $fromWarehouse = Warehouse::findOrFail($validated['from_warehouse_id']);
        $toWarehouse = Warehouse::findOrFail($validated['to_warehouse_id']);

        // Check available stock
        $stock = ProductStock::where('product_id', $product->id)
            ->where('warehouse_id', $fromWarehouse->id)
            ->first();

        $availableQty = $stock ? ($stock->quantity - $stock->reserved_quantity) : 0;

        if ($validated['quantity'] > $availableQty) {
            return back()->with('error', 'الكمية المطلوبة أكبر من الكمية المتاحة (' . number_format($availableQty, 2) . ')');
        }

        $this->inventoryService->transfer(
            product: $product,
            fromWarehouse: $fromWarehouse,
            toWarehouse: $toWarehouse,
            quantity: $validated['quantity'],
            reference: $validated['reference'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('stock.index')
            ->with('success', 'تم تحويل المخزون بنجاح');
    }

    /**
     * Get current stock for a product in a warehouse (AJAX)
     */
    public function getStock(Request $request)
    {
        $stock = ProductStock::where('product_id', $request->product_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->first();

        if (!$stock) {
            return response()->json([
                'quantity' => 0,
                'reserved' => 0,
                'available' => 0,
                'average_cost' => 0,
            ]);
        }

        return response()->json([
            'quantity' => $stock->quantity,
            'reserved' => $stock->reserved_quantity,
            'available' => $stock->quantity - $stock->reserved_quantity,
            'average_cost' => $stock->average_cost,
        ]);
    }
}
