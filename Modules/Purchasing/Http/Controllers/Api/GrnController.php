<?php

namespace Modules\Purchasing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Purchasing\Models\Grn;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Purchasing\Http\Resources\GrnResource;
use Modules\Inventory\Models\Warehouse;

/**
 * GrnController - API for Goods Received Notes
 */
class GrnController extends Controller
{
    public function __construct(
        protected PurchasingService $purchasingService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Grn::with(['supplier:id,code,name', 'warehouse:id,name', 'purchaseOrder:id,po_number'])
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->warehouse_id, fn($q, $id) => $q->where('warehouse_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('received_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('received_date', '<=', $date))
            ->orderBy('received_date', 'desc');

        $grns = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => GrnResource::collection($grns),
            'meta' => [
                'current_page' => $grns->currentPage(),
                'last_page' => $grns->lastPage(),
                'total' => $grns->total(),
            ],
        ]);
    }

    /**
     * Receive goods from a PO
     */
    public function receive(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supplier_delivery_note' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_line_id' => 'required|exists:purchase_order_lines,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        try {
            $grn = $this->purchasingService->receiveGoods(
                $po,
                $warehouse,
                $validated['items'],
                $validated['supplier_delivery_note'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Goods received successfully',
                'data' => new GrnResource($grn->load('lines.product')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(Grn $grn): JsonResponse
    {
        $grn->load(['supplier', 'warehouse', 'purchaseOrder', 'lines.product', 'receiver']);

        return response()->json([
            'success' => true,
            'data' => new GrnResource($grn),
        ]);
    }
}
