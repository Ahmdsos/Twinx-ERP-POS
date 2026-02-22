<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;

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
            $deliveryOrder = DeliveryOrder::with(['salesOrder.customer', 'customer', 'lines.product'])
                ->findOrFail($request->delivery_order_id);
        }

        // Get delivered orders without invoice
        $deliveredOrders = DeliveryOrder::where('status', DeliveryStatus::DELIVERED)
            ->whereDoesntHave('salesOrder.invoices')
            ->with(['salesOrder.customer', 'customer'])
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
     * Show form for editing invoice
     */
    public function edit(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['lines.product', 'customer']);
        $customers = Customer::orderBy('name')->get();
        $warehouses = \Modules\Inventory\Models\Warehouse::all();

        $defaultTaxRate = (float) \App\Models\Setting::getValue('default_tax_rate', 14); // Default to 14 if not set

        // Prepare data for AlpineJS to avoid Blade syntax errors
        $linesData = $salesInvoice->lines->map(function ($line) {
            return [
                'id' => $line->id,
                'product_id' => $line->product_id,
                'quantity' => (float) $line->quantity,
                'unit_price' => (float) $line->unit_price,
                'discount_percent' => (float) $line->discount_percent,
                'tax_percent' => (float) $line->tax_percent,
            ];
        });

        $productsData = \Modules\Inventory\Models\Product::where('is_active', true)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'price' => $p->selling_price,
                'name' => $p->name,
                'tax_rate' => (float) ($p->tax_rate > 0 ? $p->tax_rate : $defaultTaxRate)
            ]);

        return view('sales.invoices.edit', compact('salesInvoice', 'customers', 'warehouses', 'linesData', 'productsData', 'defaultTaxRate'));
    }

    /**
     * Update invoice
     */
    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $this->salesService->updateInvoice($salesInvoice, $validated, $validated['lines']);

        return redirect()->route('sales-invoices.show', $salesInvoice)
            ->with('success', 'تم تحديث الفاتورة بنجاح وإعادة حساب القيود');
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

    /**
     * Delete invoice (only if draft/cancelled and no payments)
     */
    public function destroy(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->paid_amount > 0) {
            return back()->with('error', 'لا يمكن حذف فاتورة تم دفع جزء منها');
        }
        if (!in_array($salesInvoice->status, [SalesInvoiceStatus::DRAFT, SalesInvoiceStatus::CANCELLED])) {
            return back()->with('error', 'لا يمكن حذف فاتورة نشطة - قم بإلغائها أولاً');
        }
        $salesInvoice->lines()->delete();
        $salesInvoice->delete();
        return redirect()->route('sales-invoices.index')->with('success', 'تم حذف الفاتورة بنجاح');
    }
}
