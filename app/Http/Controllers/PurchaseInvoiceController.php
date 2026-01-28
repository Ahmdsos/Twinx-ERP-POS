<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\PurchaseInvoiceLine;
use Modules\Purchasing\Models\Grn;
use Modules\Purchasing\Models\GrnLine;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Enums\PurchaseInvoiceStatus;
use Modules\Purchasing\Services\PurchasingService;

/**
 * PurchaseInvoiceController - Manages Purchase Invoice UI operations
 * 
 * Handles:
 * - Create invoice from GRN
 * - View invoice details
 * - Cancel invoice
 * - Print invoice
 */
class PurchaseInvoiceController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display list of purchase invoices
     */
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['supplier', 'grn', 'purchaseOrder'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        // Show overdue only
        if ($request->boolean('overdue_only')) {
            $query->overdue();
        }

        $invoices = $query->paginate(20);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $statuses = PurchaseInvoiceStatus::cases();

        // Stats
        $stats = [
            'total_pending' => PurchaseInvoice::pending()->sum('balance_due'),
            'total_overdue' => PurchaseInvoice::overdue()->sum('balance_due'),
            'overdue_count' => PurchaseInvoice::overdue()->count(),
        ];

        return view('purchasing.invoices.index', compact('invoices', 'suppliers', 'statuses', 'stats'));
    }

    /**
     * Show form for creating invoice from GRN
     */
    public function create(Request $request)
    {
        // If GRN ID is provided, load it
        $grn = null;
        if ($request->filled('grn_id')) {
            $grn = Grn::with(['supplier', 'lines.product.unit', 'purchaseOrder'])
                ->completed()
                ->find($request->grn_id);

            if (!$grn) {
                return redirect()->route('purchase-invoices.index')
                    ->with('error', 'سند الاستلام غير متاح للفوترة');
            }
        }

        // Get completed GRNs without invoices (simplified - would need more logic in real world)
        $grns = Grn::completed()
            ->with('supplier')
            ->doesntHave('purchaseOrder.invoices') // Simplified filter
            ->orderByDesc('received_date')
            ->get();

        return view('purchasing.invoices.create', compact('grn', 'grns'));
    }

    /**
     * Store new purchase invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'grn_id' => 'required|exists:grns,id',
            'supplier_invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ]);

        $grn = Grn::with(['supplier', 'lines.product', 'purchaseOrder'])->findOrFail($validated['grn_id']);

        try {
            $invoice = $this->purchasingService->createInvoice(
                $grn,
                $validated['supplier_invoice_number'],
                $validated['invoice_date'],
                $validated['due_date'],
                $validated['notes'] ?? null
            );

            return redirect()->route('purchase-invoices.show', $invoice)
                ->with('success', 'تم إنشاء فاتورة الشراء: ' . $invoice->invoice_number);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display invoice details
     */
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load([
            'supplier',
            'grn',
            'purchaseOrder',
            'lines.product.unit',
            'paymentAllocations.payment',
        ]);

        return view('purchasing.invoices.show', compact('purchaseInvoice'));
    }

    /**
     * Print invoice
     */
    public function print(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load([
            'supplier',
            'lines.product.unit',
        ]);

        return view('purchasing.invoices.print', compact('purchaseInvoice'));
    }

    /**
     * Cancel invoice
     */
    public function cancel(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status === PurchaseInvoiceStatus::PAID) {
            return back()->with('error', 'لا يمكن إلغاء فاتورة مدفوعة');
        }

        if ($purchaseInvoice->paid_amount > 0) {
            return back()->with('error', 'لا يمكن إلغاء فاتورة بها مدفوعات');
        }

        $purchaseInvoice->update(['status' => PurchaseInvoiceStatus::CANCELLED]);

        return back()->with('success', 'تم إلغاء الفاتورة');
    }
}
