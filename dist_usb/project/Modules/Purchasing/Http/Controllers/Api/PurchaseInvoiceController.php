<?php

namespace Modules\Purchasing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\Grn;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Purchasing\Http\Resources\PurchaseInvoiceResource;

/**
 * PurchaseInvoiceController - API for Purchase Invoices (Bills)
 */
class PurchaseInvoiceController extends Controller
{
    public function __construct(
        protected PurchasingService $purchasingService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $query = PurchaseInvoice::with(['supplier:id,code,name'])
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->boolean('overdue'), fn($q) => $q->overdue())
            ->when($request->date_from, fn($q, $date) => $q->whereDate('invoice_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('invoice_date', '<=', $date))
            ->orderBy('invoice_date', 'desc');

        $invoices = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => PurchaseInvoiceResource::collection($invoices),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Create invoice from GRN
     */
    public function createFromGrn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'grn_id' => 'required|exists:grns,id',
            'supplier_invoice_number' => 'nullable|string|max:50',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
        ]);

        $grn = Grn::findOrFail($validated['grn_id']);

        try {
            $invoice = $this->purchasingService->createInvoiceFromGrn(
                $grn,
                $validated['supplier_invoice_number'] ?? null,
                isset($validated['invoice_date']) ? \Carbon\Carbon::parse($validated['invoice_date']) : null,
                isset($validated['due_date']) ? \Carbon\Carbon::parse($validated['due_date']) : null
            );

            return response()->json([
                'success' => true,
                'message' => 'Purchase invoice created successfully',
                'data' => new PurchaseInvoiceResource($invoice->load('lines')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(PurchaseInvoice $purchaseInvoice): JsonResponse
    {
        $purchaseInvoice->load(['supplier', 'grn', 'purchaseOrder', 'lines.product', 'paymentAllocations.payment']);

        return response()->json([
            'success' => true,
            'data' => new PurchaseInvoiceResource($purchaseInvoice),
        ]);
    }

    public function pending(): JsonResponse
    {
        $invoices = PurchaseInvoice::with('supplier:id,code,name')
            ->pending()
            ->orderBy('due_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => PurchaseInvoiceResource::collection($invoices),
            'summary' => [
                'total_pending' => $invoices->sum('balance_due'),
                'overdue_count' => $invoices->filter(fn($i) => $i->isOverdue())->count(),
            ],
        ]);
    }
}
