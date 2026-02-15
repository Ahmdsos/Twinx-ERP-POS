<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Category;

/**
 * CategoryController
 * 
 * Handles web routes for category management.
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount(['products', 'children'])
            ->orderBy('name')
            ->get();

        return view('inventory.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')->get(); // Only main categories as parents
        return view('inventory.categories.create', compact('categories'));
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        // Eager load stock to prevent N+1 and ensure calculation is correct
        $category->load(['parent', 'children', 'products.stock']);
        return view('inventory.categories.show', compact('category'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم إضافة التصنيف بنجاح');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        return view('inventory.categories.edit', compact('category', 'categories'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Prevent self-referencing
        if ($validated['parent_id'] == $category->id) {
            return back()->with('error', 'لا يمكن أن يكون التصنيف أباً لنفسه');
        }

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم تحديث التصنيف بنجاح');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Check if category has products or children
        if ($category->products()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا التصنيف لأنه يحتوي على منتجات');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا التصنيف لأنه يحتوي على تصنيفات فرعية');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح');
    }

    /**
     * Export categories to Excel
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CategoriesExport, 'categories.xlsx');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.categories.import');
    }

    /**
     * Download sample file
     */
    public function importSample()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CategoriesExport, 'categories_sample.xlsx');
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
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\CategoriesImport, $request->file('file'));

            return redirect()->route('categories.index')
                ->with('success', 'تم استيراد التصنيفات بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }
}
