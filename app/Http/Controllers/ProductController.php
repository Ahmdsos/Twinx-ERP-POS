<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use App\Models\Brand;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Enums\ProductType;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Services\InventoryService;

/**
 * ProductController - Product management web UI
 * 
 * Field mapping (form -> database):
 * - sale_price -> selling_price
 * - min_stock_level -> min_stock
 * - Relationship: stock() not stocks()
 */
class ProductController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * List all products
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'unit', 'brand'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $products = $query->paginate(25);
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('inventory.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $types = ProductType::cases();

        return view('inventory.products.create', compact('categories', 'brands', 'units', 'warehouses', 'types'));
    }

    /**
     * Store new product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50',
            'type' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock_level' => 'numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'numeric|min:0',
            'tax_rate' => 'numeric|min:0|max:100',
            // Pricing Tiers
            'price_distributor' => 'nullable|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'price_half_wholesale' => 'nullable|numeric|min:0',
            'price_quarter_wholesale' => 'nullable|numeric|min:0',
            'price_special' => 'nullable|numeric|min:0',

            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
            // Initial stock fields
            'initial_warehouse_id' => 'nullable|exists:warehouses,id',
            'initial_stock' => 'nullable|numeric|min:0',
            // New extended fields
            'brand_id' => 'nullable|exists:brands,id',
            'warranty_months' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|in:kg,g,lb',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'dimension_unit' => 'nullable|in:cm,m,in',
            'expiry_date' => 'nullable|date',
            'country_of_origin' => 'nullable|string|max:100',
            'track_batches' => 'boolean',
            'track_serials' => 'boolean',
            // Images
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        // Map form fields to database columns
        $data = [
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'barcode' => $validated['barcode'] ?? null,
            'type' => ProductType::from($validated['type']),
            'category_id' => $validated['category_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'description' => $validated['description'] ?? null,
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['sale_price'], // Map sale_price -> selling_price
            'min_stock' => $validated['min_stock_level'] ?? 0, // Map min_stock_level -> min_stock
            'max_stock' => $validated['max_stock_level'] ?? null,
            'reorder_quantity' => $validated['reorder_quantity'] ?? 0,
            'tax_rate' => $validated['tax_rate'] ?? 14,
            'is_active' => $request->has('is_active'),
            'is_sellable' => $request->has('is_sellable'),
            'is_purchasable' => $request->has('is_purchasable'),
            // Extended fields
            'brand_id' => $validated['brand_id'] ?? null,
            'warranty_months' => $validated['warranty_months'] ?? 0,
            'weight' => $validated['weight'] ?? null,
            'weight_unit' => $validated['weight_unit'] ?? 'kg',
            'length' => $validated['length'] ?? null,
            'width' => $validated['width'] ?? null,
            'height' => $validated['height'] ?? null,
            'dimension_unit' => $validated['dimension_unit'] ?? 'cm',
            'expiry_date' => $validated['expiry_date'] ?? null,
            'country_of_origin' => $validated['country_of_origin'] ?? null,
            'track_batches' => $request->has('track_batches'),
            'track_serials' => $request->has('track_serials'),
            'created_by' => auth()->id(),
        ];

        $product = Product::create($data);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                \Modules\Inventory\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'filename' => basename($path),
                    'disk' => 'public',
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        // Create initial stock if warehouse and quantity provided
        // REFACTORED: Uses InventoryService (Single Write Path Enforcement)
        if (!empty($validated['initial_warehouse_id']) && !empty($validated['initial_stock']) && $validated['initial_stock'] > 0) {
            $warehouse = Warehouse::find($validated['initial_warehouse_id']);
            if ($warehouse) {
                $this->inventoryService->addStock(
                    $product,
                    $warehouse,
                    $validated['initial_stock'],
                    $validated['cost_price'],
                    MovementType::ADJUSTMENT_IN,
                    null,
                    'Initial stock on product creation'
                );
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'تم إنشاء المنتج بنجاح');
    }

    /**
     * Show product details
     */
    public function show(Product $product)
    {
        // Use correct relationship name: stock() not stocks()
        $product->load(['category', 'unit', 'stock.warehouse', 'brand']);

        return view('inventory.products.show', compact('product'));
    }

    /**
     * Show edit form
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $types = ProductType::cases();

        return view('inventory.products.edit', compact('product', 'categories', 'brands', 'units', 'warehouses', 'types'));
    }

    /**
     * Update product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50',
            'type' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_stock_level' => 'numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'numeric|min:0',
            'reorder_quantity' => 'numeric|min:0',
            'tax_rate' => 'numeric|min:0|max:100',
            // Pricing Tiers
            'price_distributor' => 'nullable|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'price_half_wholesale' => 'nullable|numeric|min:0',
            'price_quarter_wholesale' => 'nullable|numeric|min:0',
            'price_special' => 'nullable|numeric|min:0',

            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
            'brand_id' => 'nullable|exists:brands,id',
        ]);

        // Map form fields to database columns
        $data = [
            'name' => $validated['name'],
            'sku' => $validated['sku'],
            'barcode' => $validated['barcode'] ?? null,
            'type' => ProductType::from($validated['type']),
            'category_id' => $validated['category_id'] ?? null,
            'unit_id' => $validated['unit_id'],
            'description' => $validated['description'] ?? null,
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['sale_price'],
            'min_stock' => $validated['min_stock_level'] ?? 0,
            'max_stock' => $validated['max_stock_level'] ?? null,
            'reorder_quantity' => $validated['reorder_quantity'] ?? 0,
            'tax_rate' => $validated['tax_rate'] ?? 14,
            // Pricing Tiers
            'price_distributor' => $validated['price_distributor'] ?? 0,
            'price_wholesale' => $validated['price_wholesale'] ?? 0,
            'price_half_wholesale' => $validated['price_half_wholesale'] ?? 0,
            'price_quarter_wholesale' => $validated['price_quarter_wholesale'] ?? 0,
            'price_special' => $validated['price_special'] ?? 0,

            'is_active' => $request->has('is_active'),
            'is_sellable' => $request->has('is_sellable'),
            'is_purchasable' => $request->has('is_purchasable'),
            'brand_id' => $validated['brand_id'] ?? null,
            'updated_by' => auth()->id(),
        ];

        $product->update($data);

        // Handle image uploads
        if ($request->hasFile('images')) {
            // Get current max sort order
            $currentMaxSort = $product->images()->max('sort_order') ?? -1;

            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                \Modules\Inventory\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'filename' => basename($path),
                    'disk' => 'public',
                    'mime_type' => $image->getMimeType(),
                    'size' => $image->getSize(),
                    'is_primary' => $product->images()->doesntExist() && $index === 0, // Primary if no other images exist
                    'sort_order' => $currentMaxSort + 1 + $index,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        // Use correct relationship and column name: stock() and quantity
        if ($product->stock()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'لا يمكن حذف منتج له مخزون');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.products.import');
    }

    /**
     * Download sample CSV
     */
    public function importSample()
    {
        $headers = ['sku', 'barcode', 'name', 'description', 'category', 'unit', 'cost_price', 'selling_price', 'tax_rate', 'reorder_level'];
        $sample = ['PROD-001', '6281000000001', 'منتج تجريبي', 'وصف المنتج', '', '', '100', '150', '15', '10'];

        $content = \App\Services\CsvImportService::generateSampleCsv($headers, $sample);

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="products_sample.csv"');
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
            'sku' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ];

        \DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $validated = $importService->validateRow($row, $rules, $row['_line']);

                if ($validated) {
                    // Find category by name
                    $categoryId = null;
                    if (!empty($row['category'])) {
                        $category = Category::firstOrCreate(['name' => $row['category']]);
                        $categoryId = $category->id;
                    }

                    // Find unit by name
                    $unitId = null;
                    if (!empty($row['unit'])) {
                        $unit = Unit::firstOrCreate(['name' => $row['unit']], ['abbreviation' => $row['unit']]);
                        $unitId = $unit->id;
                    }

                    Product::updateOrCreate(
                        ['sku' => $validated['sku']],
                        [
                            'barcode' => $row['barcode'] ?? null,
                            'name' => $validated['name'],
                            'description' => $row['description'] ?? null,
                            'category_id' => $categoryId,
                            'unit_id' => $unitId,
                            'cost_price' => $validated['cost_price'] ?? 0,
                            'selling_price' => $validated['selling_price'] ?? 0,
                            'tax_rate' => $validated['tax_rate'] ?? 0,
                            'reorder_level' => $row['reorder_level'] ?? 0,
                            'is_active' => true,
                            'is_sellable' => true,
                            'is_purchasable' => true,
                        ]
                    );
                }
            }

            \DB::commit();
            $results = $importService->getResults();

            return redirect()->route('products.index')
                ->with('success', "تم استيراد {$results['success_count']} منتج بنجاح" .
                    ($results['error_count'] > 0 ? " ({$results['error_count']} أخطاء)" : ''));

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'خطأ في الاستيراد: ' . $e->getMessage());
        }
    }
}
