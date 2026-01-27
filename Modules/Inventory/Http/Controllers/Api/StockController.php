<?php

namespace Modules\Inventory\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Http\Resources\StockMovementResource;

/**
 * StockController - API for Stock operations
 */
class StockController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get stock movements with filters
     */
    public function movements(Request $request): JsonResponse
    {
        $query = StockMovement::with(['product:id,sku,name', 'warehouse:id,name', 'creator:id,name'])
            ->when($request->product_id, fn($q, $id) => $q->forProduct($id))
            ->when($request->warehouse_id, fn($q, $id) => $q->inWarehouse($id))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('movement_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('movement_date', '<=', $date))
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc');

        $movements = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => StockMovementResource::collection($movements),
            'meta' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ]);
    }

    /**
     * Add stock (receive inventory)
     */
    public function receive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        try {
            $movement = $this->inventoryService->addStock(
                $product,
                $warehouse,
                $validated['quantity'],
                $validated['unit_cost'],
                MovementType::PURCHASE,
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock received successfully',
                'data' => new StockMovementResource($movement->load(['product', 'warehouse'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Issue stock (for adjustments or manual deductions)
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0.0001',
            'reason' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        try {
            $movement = $this->inventoryService->removeStock(
                $product,
                $warehouse,
                $validated['quantity'],
                MovementType::ADJUSTMENT_OUT,
                null,
                $validated['reason'] ?? 'Manual stock issue'
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock issued successfully',
                'data' => new StockMovementResource($movement->load(['product', 'warehouse'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Transfer stock between warehouses
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.0001',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $fromWarehouse = Warehouse::findOrFail($validated['from_warehouse_id']);
        $toWarehouse = Warehouse::findOrFail($validated['to_warehouse_id']);

        try {
            $result = $this->inventoryService->transfer(
                $product,
                $fromWarehouse,
                $toWarehouse,
                $validated['quantity'],
                $validated['reference'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock transferred successfully',
                'data' => [
                    'out_movement' => new StockMovementResource($result['out']->load(['product', 'warehouse'])),
                    'in_movement' => new StockMovementResource($result['in']->load(['product', 'warehouse'])),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Adjust stock to a specific quantity
     */
    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'new_quantity' => 'required|numeric|min:0',
            'new_unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        try {
            $movement = $this->inventoryService->adjust(
                $product,
                $warehouse,
                $validated['new_quantity'],
                $validated['new_unit_cost'] ?? null,
                $validated['reason']
            );

            if (!$movement) {
                return response()->json([
                    'success' => true,
                    'message' => 'No adjustment needed - quantity unchanged',
                    'data' => null,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => new StockMovementResource($movement->load(['product', 'warehouse'])),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get movement types
     */
    public function movementTypes(): JsonResponse
    {
        $types = collect(MovementType::cases())->map(fn($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'is_inward' => $type->isInward(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
