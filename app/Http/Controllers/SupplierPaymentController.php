<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\SupplierPayment;
use Modules\Purchasing\Models\SupplierPaymentAllocation;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Accounting\Models\Account;

/**
 * SupplierPaymentController - Manages Supplier Payment UI operations
 * 
 * Handles:
 * - Create payment with invoice allocation
 * - View payment details
 * - Print payment receipt
 */
class SupplierPaymentController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display list of supplier payments
     */
    public function index(Request $request)
    {
        $query = SupplierPayment::with(['supplier', 'paymentAccount', 'creator'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Search by payment number
        if ($request->filled('search')) {
            $query->where('payment_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->paginate(20);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // Stats
        $today = now()->toDateString();
        $stats = [
            'today' => SupplierPayment::whereDate('payment_date', $today)->sum('amount'),
            'this_month' => SupplierPayment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
        ];

        return view('purchasing.payments.index', compact('payments', 'suppliers', 'stats'));
    }

    /**
     * Show form for creating a new payment
     */
    public function create(Request $request)
    {
        // Get pending invoices for suppliers
        $pendingInvoices = PurchaseInvoice::pending()
            ->with('supplier')
            ->orderBy('supplier_id')
            ->orderByDesc('due_date')
            ->get();

        // If a specific invoice is pre-selected
        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = PurchaseInvoice::with('supplier')->find($request->invoice_id);

            // Ensure selected invoice is in the list even if status query missed it (safety net)
            if ($selectedInvoice && !$pendingInvoices->contains('id', $selectedInvoice->id)) {
                $pendingInvoices->push($selectedInvoice);
            }
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        // Get cash/bank accounts for payment
        $paymentAccounts = Account::whereIn('type', ['asset'])
            ->where(function ($q) {
                $q->where('code', 'like', '11%') // Cash accounts
                    ->orWhere('code', 'like', '12%'); // Bank accounts
            })
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('purchasing.payments.create', compact(
            'pendingInvoices',
            'selectedInvoice',
            'suppliers',
            'paymentAccounts'
        ));
    }

    /**
     * Store a new payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque',
            'payment_account_id' => 'required|exists:accounts,id',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required_with:allocations|exists:purchase_invoices,id',
            'allocations.*.amount' => 'nullable|numeric|min:0',
        ]);

        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $paymentAccount = Account::findOrFail($validated['payment_account_id']);

        try {
            // Prepare allocations for service (requires invoice_id format)
            $invoiceAllocations = [];
            if (!empty($validated['allocations'])) {
                foreach ($validated['allocations'] as $alloc) {
                    // Only process allocations with a positive amount
                    if (isset($alloc['amount']) && $alloc['amount'] > 0) {
                        $invoiceAllocations[] = [
                            'invoice_id' => $alloc['invoice_id'],
                            'amount' => $alloc['amount'],
                        ];
                    }
                }
            }

            $payment = $this->purchasingService->createPayment(
                $supplier,
                $validated['amount'],
                $paymentAccount,
                $validated['payment_method'],
                $validated['reference'] ?? null,
                \Carbon\Carbon::parse($validated['payment_date']),
                $invoiceAllocations
            );

            // Additional notes - store separately if needed
            if (!empty($validated['notes'])) {
                $payment->update(['notes' => $validated['notes']]);
            }

            return redirect()->route('supplier-payments.show', $payment)
                ->with('success', 'تم تسجيل الدفعة: ' . $payment->payment_number);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display payment details
     */
    public function show(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load([
            'supplier',
            'paymentAccount',
            'creator',
            'allocations.invoice',
        ]);

        return view('purchasing.payments.show', compact('supplierPayment'));
    }

    /**
     * Print payment receipt
     */
    public function print(SupplierPayment $supplierPayment)
    {
        $supplierPayment->load([
            'supplier',
            'allocations.invoice',
        ]);

        return view('purchasing.payments.print', compact('supplierPayment'));
    }
}
