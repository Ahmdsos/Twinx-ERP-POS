<?php

namespace Modules\Sales\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Models\CustomerPayment;
use Modules\Sales\Models\Customer;
use Modules\Sales\Services\SalesService;
use Modules\Sales\Http\Resources\CustomerPaymentResource;
use Modules\Accounting\Models\Account;

/**
 * CustomerPaymentController - API for customer payments
 */
class CustomerPaymentController extends Controller
{
    public function __construct(protected SalesService $salesService)
    {
    }

    /**
     * List customer payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = CustomerPayment::query()
            ->with(['customer', 'paymentAccount'])
            ->withCount('allocations');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 25);

        return response()->json([
            'data' => CustomerPaymentResource::collection($payments),
            'meta' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
            ],
        ]);
    }

    /**
     * Receive payment from customer
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|exists:accounts,id',
            'payment_method' => 'string|max:30',
            'payment_date' => 'nullable|date',
            'reference' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required_with:allocations|exists:sales_invoices,id',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $paymentAccount = Account::findOrFail($validated['payment_account_id']);

        $payment = $this->salesService->receivePayment(
            $customer,
            $validated['amount'],
            $paymentAccount,
            $validated['payment_method'] ?? 'cash',
            $validated['reference'] ?? null,
            isset($validated['payment_date']) ? \Carbon\Carbon::parse($validated['payment_date']) : null,
            $validated['allocations'] ?? []
        );

        return response()->json([
            'message' => 'Payment received successfully',
            'data' => new CustomerPaymentResource($payment),
        ], 201);
    }

    /**
     * Show payment details
     */
    public function show(CustomerPayment $customerPayment): JsonResponse
    {
        $customerPayment->load([
            'customer',
            'paymentAccount',
            'allocations.invoice',
        ]);

        return response()->json([
            'data' => new CustomerPaymentResource($customerPayment),
            'allocated_amount' => $customerPayment->getAllocatedAmount(),
            'unallocated_amount' => $customerPayment->getUnallocatedAmount(),
        ]);
    }
}
