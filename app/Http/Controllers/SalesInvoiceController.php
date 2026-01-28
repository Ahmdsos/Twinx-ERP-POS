<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Enums\SalesInvoiceStatus;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Sales\Services\SalesService;
use Carbon\Carbon;

/**
 * SalesInvoiceController - Manages Sales Invoice UI operations
 * 
 * Handles:
 * - List and view invoices
 * - Create invoice from delivery order
 * - Payment recording
 */
class SalesInvoiceController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {
    }

    /**
     * Display list of sales invoices
     */
    public function index(Request $request)
    {
        $query = SalesInvoice::with(['customer', 'salesOrder'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Filter overdue only
        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        // Search
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $invoices = $query->paginate(20);
        $customers = Customer::orderBy('name')->get();
        $statuses = SalesInvoiceStatus::cases();

        // Summary stats
        $totalPending = SalesInvoice::pending()->sum('balance_due');
        $totalOverdue = SalesInvoice::overdue()->sum('balance_due');

        return view('sales.invoices.index', compact(
            'invoices',
            'customers',
            'statuses',
            'totalPending',
            'totalOverdue'
        ));
    }

    /**
     * Show form for creating invoice from delivery
     */
    public function create(Request $request)
    {
        $deliveryOrder = null;

        if ($request->filled('delivery_order_id')) {
            $deliveryOrder = DeliveryOrder::with(['salesOrder.customer', 'lines.product'])
                ->findOrFail($request->delivery_order_id);
        }

        // Get delivered orders without invoice
        $deliveredOrders = DeliveryOrder::where('status', DeliveryStatus::DELIVERED)
            ->whereDoesntHave('salesOrder.invoices')
            ->with('salesOrder.customer')
            ->orderByDesc('delivery_date')
            ->get();

        return view('sales.invoices.create', compact('deliveryOrder', 'deliveredOrders'));
    }

    /**
     * Store new invoice from delivery order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_order_id' => 'required|exists:delivery_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $deliveryOrder = DeliveryOrder::findOrFail($validated['delivery_order_id']);

        $invoice = $this->salesService->createInvoiceFromDelivery(
            do: $deliveryOrder,
            invoiceDate: Carbon::parse($validated['invoice_date']),
            dueDate: Carbon::parse($validated['due_date'])
        );

        return redirect()->route('sales-invoices.show', $invoice)
            ->with('success', 'تم إنشاء الفاتورة بنجاح: ' . $invoice->invoice_number);
    }

    /**
     * Display invoice details
     */
    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load([
            'customer',
            'salesOrder',
            'deliveryOrder',
            'lines.product.unit',
            'paymentAllocations.payment',
        ]);

        return view('sales.invoices.show', compact('salesInvoice'));
    }

    /**
     * Print-friendly invoice view
     */
    public function print(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load([
            'customer',
            'lines.product.unit',
        ]);

        return view('sales.invoices.print', compact('salesInvoice'));
    }

    /**
     * Cancel invoice
     */
    public function cancel(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->paid_amount > 0) {
            return back()->with('error', 'لا يمكن إلغاء فاتورة تم دفع جزء منها');
        }

        $salesInvoice->update(['status' => SalesInvoiceStatus::CANCELLED]);

        return back()->with('success', 'تم إلغاء الفاتورة');
    }
}
