<?php

namespace Modules\Purchasing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Purchasing\Http\Resources\PurchaseOrderResource;
use Modules\Purchasing\Enums\PurchaseOrderStatus;

/**
 * PurchaseOrderController - API for PO CRUD
 */
class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchasingService $purchasingService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier:id,code,name', 'warehouse:id,name'])
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('order_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('order_date', '<=', $date))
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc');

        $orders = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => PurchaseOrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $po = $this->purchasingService->createPurchaseOrder(
                $validated,
                $validated['lines']
            );

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'data' => new PurchaseOrderResource($po->load('lines.product')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'lines.product', 'approver']);

        return response()->json([
            'success' => true,
            'data' => new PurchaseOrderResource($purchaseOrder),
        ]);
    }

    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!$purchaseOrder->canEdit()) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order cannot be approved in current status',
            ], 422);
        }

        $this->purchasingService->approvePurchaseOrder($purchaseOrder);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order approved',
            'data' => new PurchaseOrderResource($purchaseOrder->fresh()),
        ]);
    }

    public function cancel(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!$purchaseOrder->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order cannot be cancelled',
            ], 422);
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::CANCELLED]);

        return response()->json([
            'success' => true,
            'message' => 'Purchase order cancelled',
        ]);
    }

    public function statuses(): JsonResponse
    {
        $statuses = collect(PurchaseOrderStatus::cases())->map(fn($s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $statuses,
        ]);
    }
}
