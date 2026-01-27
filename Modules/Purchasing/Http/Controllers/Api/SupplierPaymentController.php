<?php

namespace Modules\Purchasing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Models\SupplierPayment;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Purchasing\Http\Resources\SupplierPaymentResource;
use Modules\Accounting\Models\Account;

/**
 * SupplierPaymentController - API for Supplier Payments
 */
class SupplierPaymentController extends Controller
{
    public function __construct(
        protected PurchasingService $purchasingService
    ) {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $query = SupplierPayment::with(['supplier:id,code,name', 'paymentAccount:id,code,name'])
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->date_from, fn($q, $date) => $q->whereDate('payment_date', '>=', $date))
            ->when($request->date_to, fn($q, $date) => $q->whereDate('payment_date', '<=', $date))
            ->orderBy('payment_date', 'desc');

        $payments = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => SupplierPaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|exists:accounts,id',
            'payment_method' => 'nullable|string|max:30',
            'payment_date' => 'nullable|date',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required_with:allocations|exists:purchase_invoices,id',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $paymentAccount = Account::findOrFail($validated['payment_account_id']);

        try {
            $payment = $this->purchasingService->createPayment(
                $supplier,
                $validated['amount'],
                $paymentAccount,
                $validated['payment_method'] ?? 'bank_transfer',
                $validated['reference'] ?? null,
                isset($validated['payment_date']) ? \Carbon\Carbon::parse($validated['payment_date']) : null,
                $validated['allocations'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'data' => new SupplierPaymentResource($payment),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(SupplierPayment $supplierPayment): JsonResponse
    {
        $supplierPayment->load(['supplier', 'paymentAccount', 'allocations.invoice']);

        return response()->json([
            'success' => true,
            'data' => new SupplierPaymentResource($supplierPayment),
        ]);
    }
}
