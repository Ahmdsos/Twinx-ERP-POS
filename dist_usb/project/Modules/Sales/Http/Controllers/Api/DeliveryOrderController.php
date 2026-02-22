<?php

namespace Modules\Sales\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Services\SalesService;
use Modules\Sales\Http\Resources\DeliveryOrderResource;
use Modules\Inventory\Models\Warehouse;

/**
 * DeliveryOrderController - API for delivery order management
 */
class DeliveryOrderController extends Controller
{
    public function __construct(protected SalesService $salesService)
    {
    }

    /**
     * List delivery orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = DeliveryOrder::query()
            ->with(['customer', 'warehouse', 'salesOrder'])
            ->withCount('lines');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $orders = $query->orderByDesc('delivery_date')
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'data' => DeliveryOrderResource::collection($orders),
            'meta' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
            ],
        ]);
    }

    /**
     * Create delivery from sales order
     */
    public function deliver(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.sales_order_line_id' => 'required|exists:sales_order_lines,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'shipping_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $so = SalesOrder::findOrFail($validated['sales_order_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        try {
            $do = $this->salesService->createDelivery(
                $so,
                $warehouse,
                $validated['items'],
                $validated['shipping_method'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'message' => 'Delivery order created successfully',
                'data' => new DeliveryOrderResource($do),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Show delivery order details
     */
    public function show(DeliveryOrder $deliveryOrder): JsonResponse
    {
        $deliveryOrder->load([
            'customer',
            'warehouse',
            'salesOrder',
            'lines.product',
            'lines.stockMovement',
        ]);

        return response()->json([
            'data' => new DeliveryOrderResource($deliveryOrder),
            'total_cost' => $deliveryOrder->getTotalCost(),
        ]);
    }

    /**
     * Ship delivery order
     */
    public function ship(DeliveryOrder $deliveryOrder, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'driver_id' => 'nullable|exists:hr_delivery_drivers,id',
            'driver_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
        ]);

        if (!$this->salesService->shipDelivery($deliveryOrder, $validated)) {
            return response()->json([
                'message' => "Cannot ship DO in status: {$deliveryOrder->status->label()}",
            ], 422);
        }

        return response()->json([
            'message' => 'Delivery order shipped',
            'data' => new DeliveryOrderResource($deliveryOrder->fresh()),
        ]);
    }
}
