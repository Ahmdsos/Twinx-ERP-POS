<?php

namespace App\Http\Controllers;

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
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.warehouses.import');
    }

    /**
     * Download sample CSV
     */
    public function importSample()
    {
        $headers = ['code', 'name', 'address', 'phone', 'email'];
        $sample = ['WH-001', 'مستودع رئيسي', 'العنوان', '01000000000', 'warehouse@example.com'];

        $content = \App\Services\CsvImportService::generateSampleCsv($headers, $sample);

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="warehouses_sample.csv"');
    }

    /**
     * Process CSV import
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $importService = new \App\Services\CsvImportService();
        $rows = $importService->parseFile($request->file('csv_file'));

        $rules = [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
        ];

        \DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $validated = $importService->validateRow($row, $rules, $row['_line']);

                if ($validated) {
                    Warehouse::updateOrCreate(
                        ['code' => $validated['code']],
                        [
                            'name' => $validated['name'],
                            'address' => $row['address'] ?? null,
                            'phone' => $row['phone'] ?? null,
                            'email' => $row['email'] ?? null,
                            'is_active' => true,
                        ]
                    );
                }
            }

            \DB::commit();
            $results = $importService->getResults();

            return redirect()->route('warehouses.index')
                ->with('success', "تم استيراد {$results['success_count']} مستودع بنجاح" .
                    ($results['error_count'] > 0 ? " ({$results['error_count']} أخطاء)" : ''));

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'خطأ في الاستيراد: ' . $e->getMessage());
        }
    }
}
