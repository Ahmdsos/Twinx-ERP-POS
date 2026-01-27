<?php

namespace Modules\Sales\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Services\SalesService;
use Modules\Sales\Http\Resources\SalesInvoiceResource;

/**
 * SalesInvoiceController - API for sales invoice management
 */
class SalesInvoiceController extends Controller
{
    public function __construct(protected SalesService $salesService)
    {
    }

    /**
     * List sales invoices
     */
    public function index(Request $request): JsonResponse
    {
        $query = SalesInvoice::query()
            ->with(['customer', 'salesOrder'])
            ->withCount('lines');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->boolean('overdue', false)) {
            $query->overdue();
        }

        $invoices = $query->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'data' => SalesInvoiceResource::collection($invoices),
            'meta' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
            ],
        ]);
    }

    /**
     * Create invoice from delivery order
     */
    public function createFromDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'delivery_order_id' => 'required|exists:delivery_orders,id',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
        ]);

        $do = DeliveryOrder::findOrFail($validated['delivery_order_id']);

        $invoice = $this->salesService->createInvoiceFromDelivery(
            $do,
            isset($validated['invoice_date']) ? \Carbon\Carbon::parse($validated['invoice_date']) : null,
            isset($validated['due_date']) ? \Carbon\Carbon::parse($validated['due_date']) : null
        );

        return response()->json([
            'message' => 'Sales invoice created successfully',
            'data' => new SalesInvoiceResource($invoice),
        ], 201);
    }

    /**
     * Show invoice details
     */
    public function show(SalesInvoice $salesInvoice): JsonResponse
    {
        $salesInvoice->load([
            'customer',
            'salesOrder',
            'deliveryOrder',
            'lines.product',
            'paymentAllocations.payment',
        ]);

        return response()->json([
            'data' => new SalesInvoiceResource($salesInvoice),
            'is_overdue' => $salesInvoice->isOverdue(),
            'days_overdue' => $salesInvoice->getDaysOverdue(),
        ]);
    }

    /**
     * Get pending invoices summary
     */
    public function pending(Request $request): JsonResponse
    {
        $query = SalesInvoice::pending();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        return response()->json([
            'total_pending' => $query->sum('balance_due'),
            'count_pending' => $query->count(),
            'total_overdue' => SalesInvoice::overdue()->sum('balance_due'),
            'count_overdue' => SalesInvoice::overdue()->count(),
        ]);
    }
}
