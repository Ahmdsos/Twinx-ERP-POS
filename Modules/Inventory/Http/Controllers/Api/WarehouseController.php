<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Resources\WarehouseResource;

/**
 * WarehouseController - API for Warehouse management
 */
class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List all warehouses
     */
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::with('manager')
            ->when($request->boolean('active_only', true), fn($q) => $q->active())
            ->orderBy('name');

        $warehouses = $query->get();

        return response()->json([
            'success' => true,
            'data' => WarehouseResource::collection($warehouses),
        ]);
    }

    /**
     * Create a new warehouse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'manager_id' => 'nullable|exists:users,id',
            'is_default' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse created successfully',
            'data' => new WarehouseResource($warehouse),
        ], 201);
    }

    /**
     * Get warehouse details
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        $warehouse->load('manager');

        return response()->json([
            'success' => true,
            'data' => new WarehouseResource($warehouse),
        ]);
    }

    /**
     * Update a warehouse
     */
    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'code' => "sometimes|string|max:20|unique:warehouses,code,{$warehouse->id}",
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'manager_id' => 'nullable|exists:users,id',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            Warehouse::where('id', '!=', $warehouse->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
            'data' => new WarehouseResource($warehouse),
        ]);
    }

    /**
     * Delete a warehouse
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        // Check if warehouse has stock
        if ($warehouse->productStock()->where('quantity', '>', 0)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete warehouse with stock. Transfer stock first.',
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Warehouse deleted successfully',
        ]);
    }

    /**
     * Get stock summary for a warehouse
     */
    public function stock(Warehouse $warehouse): JsonResponse
    {
        $stock = $warehouse->productStock()
            ->with('product:id,sku,name')
            ->where('quantity', '>', 0)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'total_items' => $stock->count(),
                'total_value' => $stock->sum('total_cost'),
                'products' => $stock->map(fn($s) => [
                    'product_id' => $s->product_id,
                    'sku' => $s->product->sku,
                    'name' => $s->product->name,
                    'quantity' => (float) $s->quantity,
                    'available' => (float) $s->available_quantity,
                    'average_cost' => (float) $s->average_cost,
                    'total_value' => (float) $s->total_cost,
                ]),
            ],
        ]);
    }
}
