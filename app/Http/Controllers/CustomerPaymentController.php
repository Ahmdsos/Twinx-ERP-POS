<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\CustomerPayment;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Enums\SalesInvoiceStatus;
use Modules\Sales\Services\SalesService;
use Modules\Accounting\Models\Account;
use Carbon\Carbon;

/**
 * CustomerPaymentController - Manages Customer Payment UI operations
 * 
 * Handles:
 * - Record payments from customers
 * - Allocate payments to invoices
 * - Print receipts
 */
class CustomerPaymentController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {
    }

    /**
     * Display list of customer payments
     */
    public function index(Request $request)
    {
        $query = CustomerPayment::with(['customer', 'paymentAccount'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by date range
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

        // Search
        if ($request->filled('search')) {
            $query->where('receipt_number', 'like', '%' . $request->search . '%');
        }

        $payments = $query->paginate(20);
        $customers = Customer::orderBy('name')->get();

        // Summary
        $totalToday = CustomerPayment::whereDate('payment_date', today())->sum('amount');
        $totalMonth = CustomerPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        return view('sales.payments.index', compact(
            'payments',
            'customers',
            'totalToday',
            'totalMonth'
        ));
    }

    /**
     * Show form for creating payment
     */
    public function create(Request $request)
    {
        $invoice = null;
        $customer = null;

        // Pre-select invoice if provided
        if ($request->filled('invoice_id')) {
            $invoice = SalesInvoice::with('customer')
                ->findOrFail($request->invoice_id);
            $customer = $invoice->customer;
        }

        // Pre-select customer if provided
        if ($request->filled('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }

        $customers = Customer::orderBy('name')->get();

        // Get bank/cash accounts for payment
        $paymentAccounts = Account::whereIn('type', ['asset'])
            ->where('code', 'like', '1%') // Only asset accounts
            ->orderBy('name')
            ->get();

        // Get pending invoices for selected customer
        $pendingInvoices = collect();
        if ($customer) {
            $pendingInvoices = SalesInvoice::where('customer_id', $customer->id)
                ->pending()
                ->orderBy('due_date')
                ->get();
        }

        return view('sales.payments.create', compact(
            'invoice',
            'customer',
            'customers',
            'paymentAccounts',
            'pendingInvoices'
        ));
    }

    /**
     * Store new payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card',
            'payment_account_id' => 'required|exists:accounts,id',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            // Allocations
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'exists:sales_invoices,id',
            'allocations.*.amount' => 'numeric|min:0',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $paymentAccount = Account::findOrFail($validated['payment_account_id']);

        // Prepare allocations
        $invoiceAllocations = [];
        if (!empty($validated['allocations'])) {
            foreach ($validated['allocations'] as $alloc) {
                if (!empty($alloc['amount']) && $alloc['amount'] > 0) {
                    $invoiceAllocations[$alloc['invoice_id']] = $alloc['amount'];
                }
            }
        }

        $payment = $this->salesService->receivePayment(
            customer: $customer,
            amount: $validated['amount'],
            paymentMethod: $validated['payment_method'],
            paymentAccount: $paymentAccount,
            paymentDate: Carbon::parse($validated['payment_date']),
            invoiceAllocations: $invoiceAllocations,
            reference: $validated['reference'] ?? null
        );

        return redirect()->route('customer-payments.show', $payment)
            ->with('success', 'تم تسجيل الدفعة بنجاح: ' . $payment->receipt_number);
    }

    /**
     * Display payment details
     */
    public function show(CustomerPayment $customerPayment)
    {
        $customerPayment->load([
            'customer',
            'paymentAccount',
            'allocations.invoice',
            'creator',
        ]);

        return view('sales.payments.show', compact('customerPayment'));
    }

    /**
     * Print payment receipt
     */
    public function print(CustomerPayment $customerPayment)
    {
        $customerPayment->load([
            'customer',
            'allocations.invoice',
        ]);

        return view('sales.payments.print', compact('customerPayment'));
    }

    /**
     * Get pending invoices for customer (AJAX)
     */
    public function getCustomerInvoices(Customer $customer)
    {
        $invoices = SalesInvoice::where('customer_id', $customer->id)
            ->pending()
            ->orderBy('due_date')
            ->get()
            ->map(function ($inv) {
                return [
                    'id' => $inv->id,
                    'number' => $inv->invoice_number,
                    'date' => $inv->invoice_date->format('Y-m-d'),
                    'due_date' => $inv->due_date->format('Y-m-d'),
                    'total' => $inv->total,
                    'balance' => $inv->balance_due,
                    'overdue' => $inv->isOverdue(),
                    'days_overdue' => $inv->getDaysOverdue(),
                ];
            });

        return response()->json($invoices);
    }
}
