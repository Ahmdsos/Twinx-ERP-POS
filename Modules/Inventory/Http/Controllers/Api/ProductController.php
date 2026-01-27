<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Enums\ProductType;
use Modules\Inventory\Http\Requests\StoreProductRequest;
use Modules\Inventory\Http\Requests\UpdateProductRequest;
use Modules\Inventory\Http\Resources\ProductResource;

/**
 * ProductController - API for Product CRUD operations
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List all products with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'unit'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->boolean('active_only', true), fn($q) => $q->active())
            ->when($request->boolean('sellable'), fn($q) => $q->sellable())
            ->when($request->boolean('purchasable'), fn($q) => $q->purchasable());

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get product types
     */
    public function types(): JsonResponse
    {
        $types = collect(ProductType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'tracks_inventory' => $type->tracksInventory(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Create a new product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load(['category', 'unit']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
        ], 201);
    }

    /**
     * Get product details
     */
    public function show(Product $product): JsonResponse
    {
        $product->load([
            'category',
            'unit',
            'purchaseUnit',
            'salesAccount',
            'purchaseAccount',
            'inventoryAccount',
            'stock.warehouse',
        ]);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update a product
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        $product->load(['category', 'unit']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product): JsonResponse
    {
        // Check if product has stock movements
        if ($product->stockMovements()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product with stock history. Deactivate it instead.',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Get product stock across warehouses
     */
    public function stock(Product $product): JsonResponse
    {
        $stock = $product->stock()->with('warehouse')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'total_quantity' => $stock->sum('quantity'),
                'total_available' => $stock->sum('available_quantity'),
                'total_value' => $stock->sum('total_cost'),
                'warehouses' => $stock->map(fn($s) => [
                    'warehouse_id' => $s->warehouse_id,
                    'warehouse_name' => $s->warehouse->name,
                    'quantity' => (float) $s->quantity,
                    'reserved' => (float) $s->reserved_quantity,
                    'available' => (float) $s->available_quantity,
                    'average_cost' => (float) $s->average_cost,
                    'total_value' => (float) $s->total_cost,
                ]),
            ],
        ]);
    }
}
