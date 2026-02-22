<?php

namespace Modules\Purchasing\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\Supplier;
use App\Exports\SuppliersExport;
use App\Imports\SuppliersImport;
use Maatwebsite\Excel\Facades\Excel;

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

        // Stats Logic
        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'total_debt' => Supplier::get()->sum(fn($s) => $s->getOutstandingBalance()),
            'monthly_purchases' => (float) (\Modules\Purchasing\Models\PurchaseInvoice::whereMonth('invoice_date', now()->month)->sum('total') - \Modules\Purchasing\Models\PurchaseReturn::whereMonth('return_date', now()->month)->where('status', 'approved')->sum('total_amount'))
        ];

        return view('purchasing.suppliers.index', compact('suppliers', 'stats'));
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
        // Truth: Get financial summary from Ledger
        $totalPurchasesRaw = $supplier->purchaseInvoices()->sum('total') ?? 0;
        $totalReturns = \Modules\Purchasing\Models\PurchaseReturn::where('supplier_id', $supplier->id)->where('status', 'approved')->sum('total_amount') ?? 0;
        $totalPurchases = $totalPurchasesRaw - $totalReturns;

        $balance = $supplier->getLedgerBalance();
        $totalPaid = max(0, $totalPurchases - $balance);

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

    /**
     * Display supplier account statement
     */
    public function statement(Request $request, Supplier $supplier)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Truth: Get opening balance (Sum Credits - Sum Debits) before start date from Ledger
        $openingBalance = (float) \Modules\Accounting\Models\JournalEntryLine::where('subledger_type', Supplier::class)
            ->where('subledger_id', $supplier->id)
            ->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereDate('entry_date', '<', $startDate)
                    ->where('status', \Modules\Accounting\Enums\JournalStatus::POSTED);
            })
            ->selectRaw('SUM(credit) - SUM(debit) as balance')
            ->value('balance') ?? 0;

        // Get ledger lines in date range
        $ledgerLines = \Modules\Accounting\Models\JournalEntryLine::with('journalEntry')
            ->where('subledger_type', Supplier::class)
            ->where('subledger_id', $supplier->id)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->whereDate('entry_date', '>=', $startDate)
                    ->whereDate('entry_date', '<=', $endDate)
                    ->where('status', \Modules\Accounting\Enums\JournalStatus::POSTED);
            })
            ->get();

        // Map ledger lines to transaction format
        $transactions = $ledgerLines->map(function ($line) {
            $entry = $line->journalEntry;
            return [
                'date' => $entry->entry_date,
                // If debit > 0, it's a reduction (Payment/Return)
                // If credit > 0, it's an increase (Invoice/Adjustment)
                'type' => $line->credit > 0 ? 'invoice' : 'payment',
                'reference' => $entry->reference ?? 'JE-' . $entry->id,
                'description' => $line->description ?? $entry->description,
                'debit' => $line->debit,   // Payment/Debit (Reduces Liability)
                'credit' => $line->credit, // Invoice/Credit (Increases Liability)
            ];
        })->sortBy('date');

        // Calculate running balance
        $balance = $openingBalance;
        $transactionsContent = collect();
        foreach ($transactions as $item) {
            $balance += ($item['credit'] - $item['debit']);
            $item['balance'] = $balance;
            $transactionsContent->push($item);
        }

        $closingBalance = $balance;
        $transactions = $transactionsContent;

        return view('purchasing.suppliers.statement', compact(
            'supplier',
            'transactions',
            'openingBalance',
            'closingBalance',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show import form
     */
    /**
     * Export suppliers to Excel
     */
    public function export()
    {
        return Excel::download(new SuppliersExport, 'suppliers.xlsx');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('purchasing.suppliers.import');
    }

    /**
     * Process import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new SuppliersImport, $request->file('file'));

            return redirect()->route('suppliers.index')
                ->with('success', 'تم استيراد الموردين بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }

    /**
     * Download sample file
     */
    public function importSample()
    {
        return Excel::download(new SuppliersExport, 'suppliers_sample.xlsx');
    }
}
