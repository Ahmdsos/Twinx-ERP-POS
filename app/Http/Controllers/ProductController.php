<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Enums\ProductType;

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
    /**
     * List all products
     */
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'unit'])
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

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $products = $query->paginate(25);
        $categories = Category::orderBy('name')->get();

        return view('inventory.products.index', compact('products', 'categories'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $types = ProductType::cases();

        return view('inventory.products.create', compact('categories', 'units', 'types'));
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
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
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
            'created_by' => auth()->id(),
        ];

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'تم إنشاء المنتج بنجاح');
    }

    /**
     * Show product details
     */
    public function show(Product $product)
    {
        // Use correct relationship name: stock() not stocks()
        $product->load(['category', 'unit', 'stock.warehouse']);

        return view('inventory.products.show', compact('product'));
    }

    /**
     * Show edit form
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $types = ProductType::cases();

        return view('inventory.products.edit', compact('product', 'categories', 'units', 'types'));
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
            'is_active' => 'boolean',
            'is_sellable' => 'boolean',
            'is_purchasable' => 'boolean',
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
            'is_active' => $request->has('is_active'),
            'is_sellable' => $request->has('is_sellable'),
            'is_purchasable' => $request->has('is_purchasable'),
            'updated_by' => auth()->id(),
        ];

        $product->update($data);

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
}
