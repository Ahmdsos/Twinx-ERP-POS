<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Brand;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Enums\ProductType;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Services\InventoryService;
use App\Services\ImportExportService;
use App\Exports\ProductsExport;
use App\Exports\ProductsSheet;
use App\Imports\ProductsImport;
use App\Imports\ProductsSheetImport;

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
            ->withSum('stock as total_stock_qty', 'quantity');

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('brand', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Category & Brand Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Stock Status Filter
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;

            if ($status === 'in_stock') {
                $query->where(function ($q) {
                    $q->selectRaw('SUM(quantity)')
                        ->from('product_stock')
                        ->whereColumn('product_id', 'products.id');
                }, '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->selectRaw('SUM(quantity)')
                            ->from('product_stock')
                            ->whereColumn('product_id', 'products.id');
                    }, '<=', 0)
                        ->orWhereNotExists(function ($sub) {
                            $sub->selectRaw(1)
                                ->from('product_stock')
                                ->whereColumn('product_id', 'products.id');
                        });
                });
            } elseif ($status === 'low_stock') {
                $query->where(function ($q) {
                    $q->selectRaw('SUM(quantity)')
                        ->from('product_stock')
                        ->whereColumn('product_id', 'products.id');
                }, '<=', \Illuminate\Support\Facades\DB::raw('products.reorder_level'));
            }
        }

        // Activity Filter
        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        // Advanced Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');

        $allowedSorts = ['id', 'sku', 'barcode', 'name', 'selling_price', 'cost_price', 'total_stock_qty'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(25)->withQueryString();
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
                    MovementType::INITIAL,
                    null,
                    'Initial stock on product creation'
                );
            }
        }

        // Auto-generate numeric barcode if not provided
        if (empty($product->barcode)) {
            $barcodeService = app(\App\Services\BarcodeService::class);
            $product->update(['barcode' => $barcodeService->generateAutoBarcode($product)]);
        }

        // Redirect: if Save & Print was clicked, go to barcode print page
        if ($request->input('_print_barcode') == '1') {
            return redirect()->route('barcode.print', $product)
                ->with('success', 'تم إنشاء المنتج بنجاح');
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

        // Auto-generate barcode if empty after update
        if (empty($product->barcode)) {
            $barcodeService = app(\App\Services\BarcodeService::class);
            $product->update(['barcode' => $barcodeService->generateAutoBarcode($product)]);
        }

        // Redirect: if Save & Print was clicked, go to barcode print page
        if ($request->input('_print_barcode') == '1') {
            return redirect()->route('barcode.print', $product)
                ->with('success', 'تم تحديث المنتج بنجاح');
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
     * Export products to Excel or CSV
     */
    public function export(Request $request, ImportExportService $service)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'products_' . date('Y-m-d_H-i') . '.' . $format;

        if ($format === 'json') {
            $jsonData = app(\App\Services\InventoryJsonService::class)->getData();
            return response()->json($jsonData)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }

        // Use multi-sheet only for XLSX
        $export = ($format === 'xlsx') ? new ProductsExport : new ProductsSheet();

        return $service->export($export, $filename);
    }

    /**
     * Import products from Excel or CSV
     */
    public function import(Request $request, ImportExportService $service)
    {
        $request->validate([
            'file' => 'required|file', // mimes might fail for some JSON types depending on server config
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            if ($extension === 'json') {
                $data = json_decode($file->get(), true);
                if (!$data)
                    throw new \Exception('Invalid JSON format');
                app(\App\Services\InventoryJsonService::class)->importData($data);
                return redirect()->back()->with('success', 'تم استيراد البيانات من ملف JSON بنجاح');
            }

            // SMART DETECTION:
            // .xlsx -> Super-Excel (Multi-Sheet)
            // .csv  -> Single-Sheet (Products Only)
            $importer = ($extension === 'xlsx' || $extension === 'xls')
                ? new ProductsImport
                : new ProductsSheetImport();

            $service->import($importer, $file);
            return redirect()->back()->with('success', 'تم استيراد المنتجات بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'فشل الاستيراد: ' . $e->getMessage());
        }
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('inventory.products.import');
    }

    /**
     * Download sample Template
     */
    public function importSample(ImportExportService $service)
    {
        // Return a full template
        return $service->export(new ProductsExport, 'products_template.xlsx');
    }
}