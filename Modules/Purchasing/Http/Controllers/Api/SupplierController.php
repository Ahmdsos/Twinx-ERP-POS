<?php

namespace Modules\Purchasing\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Http\Resources\SupplierResource;

/**
 * SupplierController - API for Supplier CRUD
 */
class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query()
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->boolean('active_only', true), fn($q) => $q->active())
            ->orderBy('name');

        $suppliers = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => SupplierResource::collection($suppliers),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'total' => $suppliers->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'integer|min:0',
            'credit_limit' => 'numeric|min:0',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'data' => new SupplierResource($supplier),
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'integer|min:0',
            'credit_limit' => 'numeric|min:0',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete supplier with purchase orders',
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }

    public function balance(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'supplier_id' => $supplier->id,
                'name' => $supplier->name,
                'total_purchases' => $supplier->getTotalPurchases(),
                'outstanding_balance' => $supplier->getOutstandingBalance(),
                'credit_limit' => (float) $supplier->credit_limit,
            ],
        ]);
    }
}
