<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\Customer;

/**
 * CustomerController - Customer management web UI
 */
class CustomerController extends Controller
{
    /**
     * List all customers
     */
    public function index(Request $request)
    {
        $query = Customer::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        $customers = $query->paginate(25);

        return view('sales.customers.index', compact('customers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('sales.customers.create');
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
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

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'تم إنشاء العميل بنجاح');
    }

    /**
     * Show customer details
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'salesOrders' => function ($q) {
                $q->latest()->limit(10);
            }
        ]);

        return view('sales.customers.show', compact('customer'));
    }

    /**
     * Show edit form
     */
    public function edit(Customer $customer)
    {
        return view('sales.customers.edit', compact('customer'));
    }

    /**
     * Update customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
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

        return redirect()->route('customers.index')
            ->with('success', 'تم تحديث العميل بنجاح');
    }

    /**
     * Delete customer
     */
    public function destroy(Customer $customer)
    {
        if ($customer->salesOrders()->exists()) {
            return back()->with('error', 'لا يمكن حذف عميل له أوامر بيع');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'تم حذف العميل بنجاح');
    }
}
