<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ProductStock;

/**
 * WarehouseController
 * 
 * Handles web routes for warehouse management.
 * Uses correct relationship: productStock() not stocks()
 */
class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses.
     */
    public function index()
    {
        $warehouses = Warehouse::withCount('productStock')->get();

        // Efficiently fetch stock values grouped by warehouse
        $stockValues = ProductStock::selectRaw('warehouse_id, SUM(quantity * average_cost) as total_value')
            ->groupBy('warehouse_id')
            ->pluck('total_value', 'warehouse_id');

        $warehouses->map(function ($warehouse) use ($stockValues) {
            $warehouse->stock_value = $stockValues[$warehouse->id] ?? 0;
            $warehouse->stocks_count = $warehouse->product_stock_count; // Access audit count
            return $warehouse;
        });

        // Global Summary
        $totalItems = ProductStock::where('quantity', '>', 0)->count();
        $totalValue = $stockValues->sum();

        return view('inventory.warehouses.index', compact('warehouses', 'totalItems', 'totalValue'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        return view('inventory.warehouses.create');
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') || !isset($validated['is_active']);
        $validated['is_default'] = $request->has('is_default');

        // If this is set as default, unset others
        if ($validated['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'تم إضافة المستودع بنجاح');
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse)
    {
        $stocks = $warehouse->productStock()
            ->with('product')
            ->where('quantity', '>', 0)
            ->orderBy('quantity', 'desc')
            ->paginate(20);

        $totalValue = $warehouse->productStock()
            ->selectRaw('SUM(quantity * average_cost) as value')
            ->value('value') ?? 0;

        // Add stocks_count for the view
        $warehouse->stocks_count = $warehouse->productStock()->count();

        return view('inventory.warehouses.show', compact('warehouse', 'stocks', 'totalValue'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse)
    {
        return view('inventory.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified warehouse.
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default'] = $request->has('is_default');

        // If this is set as default, unset others
        if ($validated['is_default'] && !$warehouse->is_default) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'تم تحديث المستودع بنجاح');
    }

    /**
     * Remove the specified warehouse.
     */
    public function destroy(Warehouse $warehouse)
    {
        // Cannot delete default warehouse
        if ($warehouse->is_default) {
            return back()->with('error', 'لا يمكن حذف المستودع الافتراضي');
        }

        // Check if warehouse has stock
        if ($warehouse->productStock()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا المستودع لأنه يحتوي على مخزون');
        }

        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'تم حذف المستودع بنجاح');
    }

    /**
     * Export warehouses to Excel
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WarehousesExport, 'warehouses.xlsx');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.warehouses.import');
    }

    /**
     * Download sample file
     */
    public function importSample()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\WarehousesExport, 'warehouses_sample.xlsx');
    }

    /**
     * Process import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\WarehousesImport, $request->file('file'));

            return redirect()->route('warehouses.index')
                ->with('success', 'تم استيراد المستودعات بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }
}
