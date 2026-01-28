<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\Supplier;

/**
 * SupplierController
 * 
 * Handles web routes for supplier management.
 * Uses the Supplier model from the Purchasing module.
 */
class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $suppliers = $query->orderBy('name')->paginate(20);

        return view('purchasing.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('purchasing.suppliers.create');
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'تم إضافة المورد بنجاح');
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier)
    {
        // Get financial summary using correct relationship names
        $totalPurchases = $supplier->purchaseInvoices()->sum('total') ?? 0;
        $totalPaid = $supplier->payments()->sum('amount') ?? 0;
        $balance = $totalPurchases - $totalPaid;

        $recentInvoices = $supplier->purchaseInvoices()
            ->latest('invoice_date')
            ->take(5)
            ->get();

        return view('purchasing.suppliers.show', compact(
            'supplier',
            'totalPurchases',
            'totalPaid',
            'balance',
            'recentInvoices'
        ));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('purchasing.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', 'تم تحديث بيانات المورد بنجاح');
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has any related records
        if ($supplier->purchaseOrders()->exists() || $supplier->purchaseInvoices()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا المورد لأنه مرتبط ببيانات أخرى');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'تم حذف المورد بنجاح');
    }
}
