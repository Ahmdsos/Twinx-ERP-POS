<?php

namespace Modules\Sales\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sales\Models\Customer;
use Modules\Sales\Http\Resources\CustomerResource;

/**
 * CustomerController - API for customer management
 */
class CustomerController extends Controller
{
    /**
     * List all customers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query()->with(['salesRep']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        $customers = $query->orderBy('name')->paginate($request->per_page ?? 25);

        return response()->json([
            'data' => CustomerResource::collection($customers),
            'meta' => [
                'total' => $customers->total(),
                'per_page' => $customers->perPage(),
                'current_page' => $customers->currentPage(),
            ],
        ]);
    }

    /**
     * Create a new customer
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'in:individual,company',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string|max:100',
            'billing_country' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',
            'shipping_city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'integer|min:0',
            'credit_limit' => 'numeric|min:0',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $customer = Customer::create($validated);

        return response()->json([
            'message' => 'Customer created successfully',
            'data' => new CustomerResource($customer),
        ], 201);
    }

    /**
     * Show customer details
     */
    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['salesRep', 'account']);

        return response()->json([
            'data' => new CustomerResource($customer),
            'summary' => [
                'total_sales' => $customer->getTotalSales(),
                'outstanding_balance' => $customer->getOutstandingBalance(),
                'available_credit' => $customer->getAvailableCredit(),
            ],
        ]);
    }

    /**
     * Update customer
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',
            'shipping_city' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'integer|min:0',
            'credit_limit' => 'numeric|min:0',
            'is_active' => 'boolean',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $customer->update($validated);

        return response()->json([
            'message' => 'Customer updated successfully',
            'data' => new CustomerResource($customer->fresh()),
        ]);
    }

    /**
     * Delete customer (soft delete)
     */
    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->salesOrders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete customer with sales orders',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer archived successfully',
        ]);
    }

    /**
     * Get customer balance and credit info
     */
    public function balance(Customer $customer): JsonResponse
    {
        return response()->json([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'credit_limit' => $customer->credit_limit,
            'total_sales' => $customer->getTotalSales(),
            'outstanding_balance' => $customer->getOutstandingBalance(),
            'available_credit' => $customer->getAvailableCredit(),
        ]);
    }
}
