<?php

namespace Modules\Sales\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Services\SalesService;
use Modules\Sales\Enums\SalesOrderStatus;
use Modules\Sales\Http\Resources\SalesOrderResource;

/**
 * SalesOrderController - API for sales order management
 */
class SalesOrderController extends Controller
{
    public function __construct(protected SalesService $salesService)
    {
    }

    /**
     * List sales orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = SalesOrder::query()
            ->with(['customer', 'warehouse'])
            ->withCount('lines');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->date_to);
        }

        $orders = $query->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'data' => SalesOrderResource::collection($orders),
            'meta' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
            ],
        ]);
    }

    /**
     * Create a new sales order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'date',
            'expected_date' => 'nullable|date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'customer_notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string|max:50',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'nullable|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
        ]);

        $so = $this->salesService->createSalesOrder($validated, $validated['lines']);

        return response()->json([
            'message' => 'Sales order created successfully',
            'data' => new SalesOrderResource($so),
        ], 201);
    }

    /**
     * Show sales order details
     */
    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->load(['customer', 'warehouse', 'lines.product', 'deliveryOrders', 'invoices']);

        return response()->json([
            'data' => new SalesOrderResource($salesOrder),
            'delivery_percentage' => $salesOrder->getDeliveredPercentage(),
        ]);
    }

    /**
     * Confirm a sales order
     */
    public function confirm(SalesOrder $salesOrder): JsonResponse
    {
        if (!$salesOrder->canEdit()) {
            return response()->json([
                'message' => "Cannot confirm SO in status: {$salesOrder->status->label()}",
            ], 422);
        }

        $this->salesService->confirmSalesOrder($salesOrder);

        return response()->json([
            'message' => 'Sales order confirmed',
            'data' => new SalesOrderResource($salesOrder->fresh()),
        ]);
    }

    /**
     * Cancel a sales order
     */
    public function cancel(SalesOrder $salesOrder): JsonResponse
    {
        if (!$salesOrder->canCancel()) {
            return response()->json([
                'message' => "Cannot cancel SO in status: {$salesOrder->status->label()}",
            ], 422);
        }

        $salesOrder->update(['status' => SalesOrderStatus::CANCELLED]);

        return response()->json([
            'message' => 'Sales order cancelled',
        ]);
    }

    /**
     * Get available statuses
     */
    public function statuses(): JsonResponse
    {
        return response()->json([
            'data' => collect(SalesOrderStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }
}
