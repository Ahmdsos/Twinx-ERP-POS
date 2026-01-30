<?php

namespace App\Http\Controllers;

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
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.categories.import');
    }

    /**
     * Download sample CSV
     */
    public function importSample()
    {
        $headers = ['name', 'parent', 'description'];
        $sample = ['تصنيف جديد', '', 'وصف التصنيف'];

        $content = \App\Services\CsvImportService::generateSampleCsv($headers, $sample);

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="categories_sample.csv"');
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
            'name' => 'required|string|max:255',
        ];

        \DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $validated = $importService->validateRow($row, $rules, $row['_line']);

                if ($validated) {
                    // Find parent by name
                    $parentId = null;
                    if (!empty($row['parent'])) {
                        $parent = Category::where('name', $row['parent'])->first();
                        $parentId = $parent?->id;
                    }

                    Category::firstOrCreate(
                        ['name' => $validated['name']],
                        [
                            'parent_id' => $parentId,
                            'description' => $row['description'] ?? null,
                            'is_active' => true,
                        ]
                    );
                }
            }

            \DB::commit();
            $results = $importService->getResults();

            return redirect()->route('categories.index')
                ->with('success', "تم استيراد {$results['success_count']} تصنيف بنجاح" .
                    ($results['error_count'] > 0 ? " ({$results['error_count']} أخطاء)" : ''));

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'خطأ في الاستيراد: ' . $e->getMessage());
        }
    }
}
