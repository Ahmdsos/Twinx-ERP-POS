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

        // Stats Logic
        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'total_debt' => Supplier::get()->sum(fn($s) => $s->getOutstandingBalance()),
            'monthly_purchases' => \Modules\Purchasing\Models\PurchaseInvoice::whereMonth('invoice_date', now()->month)->sum('total')
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

    /**
     * Display supplier account statement
     */
    public function statement(Request $request, Supplier $supplier)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Get opening balance (before start date)
        $openingBalance = $supplier->purchaseInvoices()
            ->whereDate('invoice_date', '<', $startDate)
            ->sum('balance_due');

        // Get invoices in date range
        $invoices = $supplier->purchaseInvoices()
            ->whereDate('invoice_date', '>=', $startDate)
            ->whereDate('invoice_date', '<=', $endDate)
            ->orderBy('invoice_date')
            ->get();

        // Get payments in date range
        $payments = $supplier->payments()
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate)
            ->orderBy('payment_date')
            ->get();

        // Combine and sort transactions
        $transactions = collect();

        foreach ($invoices as $inv) {
            $transactions->push([
                'date' => $inv->invoice_date,
                'type' => 'invoice',
                'reference' => $inv->invoice_number ?? $inv->number ?? 'INV-' . $inv->id,
                'description' => 'فاتورة مشتريات',
                'debit' => $inv->total,
                'credit' => 0,
            ]);
        }

        foreach ($payments as $pmt) {
            $transactions->push([
                'date' => $pmt->payment_date,
                'type' => 'payment',
                'reference' => $pmt->receipt_number ?? 'PMT-' . $pmt->id,
                'description' => 'دفعة للمورد',
                'debit' => 0,
                'credit' => $pmt->amount,
            ]);
        }

        $transactions = $transactions->sortBy('date');

        // Calculate running balance
        $balance = $openingBalance;
        $transactions = $transactions->map(function ($item) use (&$balance) {
            $balance += $item['debit'] - $item['credit'];
            $item['balance'] = $balance;
            return $item;
        });

        $closingBalance = $balance;

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
    public function importForm()
    {
        return view('purchasing.suppliers.import');
    }

    /**
     * Download sample CSV
     */
    public function importSample()
    {
        $headers = ['code', 'name', 'email', 'phone', 'address', 'tax_number', 'contact_person', 'payment_terms'];
        $sample = ['SUP-001', 'مورد تجريبي', 'supplier@example.com', '01000000000', 'العنوان', '', 'محمد', '30'];

        $content = \App\Services\CsvImportService::generateSampleCsv($headers, $sample);

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="suppliers_sample.csv"');
    }

    /**
     * Process CSV import
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $importService = new \App\Services\CsvImportService();
        $rows = $importService->parseFile($request->file('csv_file'));

        $rules = [
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
        ];

        \DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $validated = $importService->validateRow($row, $rules, $row['_line']);

                if ($validated) {
                    Supplier::updateOrCreate(
                        ['code' => $validated['code']],
                        [
                            'name' => $validated['name'],
                            'email' => $validated['email'] ?? null,
                            'phone' => $row['phone'] ?? null,
                            'address' => $row['address'] ?? null,
                            'tax_number' => $row['tax_number'] ?? null,
                            'contact_person' => $row['contact_person'] ?? null,
                            'payment_terms' => $row['payment_terms'] ?? 30,
                            'is_active' => true,
                        ]
                    );
                }
            }

            \DB::commit();
            $results = $importService->getResults();

            return redirect()->route('suppliers.index')
                ->with('success', "تم استيراد {$results['success_count']} مورد بنجاح" .
                    ($results['error_count'] > 0 ? " ({$results['error_count']} أخطاء)" : ''));

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'خطأ في الاستيراد: ' . $e->getMessage());
        }
    }
}
