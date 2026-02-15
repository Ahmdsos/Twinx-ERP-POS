<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Inventory\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::orderBy('name')->withCount('products')->get();
        // Note: products_count will only work if we have the relationship set up. 
        // Since Product currently (likely) stores brand as string, this might need adjustment later.
        // For now, we'll assume we might migrate Product brand to ID later, or just list brands management.

        return view('inventory.brands.index', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:brands,name',
            'description' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Brand::create($validated);

        return redirect()->route('brands.index')->with('success', 'تم إضافة العلامة التجارية بنجاح');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:brands,name,' . $brand->id,
            'description' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $brand->update($validated);

        return redirect()->route('brands.index')->with('success', 'تم تحديث البيانات بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {
        // TODO: Check for product dependencies if/when products are linked by ID
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'تم حذف العلامة التجارية بنجاح');
    }
    /**
     * Export brands to Excel
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BrandsExport, 'brands.xlsx');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.brands.import');
    }

    /**
     * Download sample file
     */
    public function importSample()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BrandsExport, 'brands_sample.xlsx');
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
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\BrandsImport, $request->file('file'));

            return redirect()->route('brands.index')
                ->with('success', 'تم استيراد العلامات التجارية بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }
}
