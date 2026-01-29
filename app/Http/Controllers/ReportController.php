<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;
use Carbon\Carbon;

/**
 * ReportController - تقارير ملخصة ومالية
 */
class ReportController extends Controller
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {
    }

    /**
     * Trial Balance Report
     * ميزان المراجعة
     */
    public function trialBalance(Request $request)
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : now();

        $trialBalance = $this->ledgerService->getTrialBalance($asOfDate);
        $totals = $this->ledgerService->getTrialBalanceTotals($asOfDate);

        return view('reports.trial-balance', compact('trialBalance', 'totals', 'asOfDate'));
    }

    /**
     * Profit & Loss Statement
     * قائمة الدخل
     */
    public function profitAndLoss(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now();

        // Get revenue accounts
        $revenues = Account::where('type', AccountType::REVENUE)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $balance = $this->ledgerService->calculateBalance($account->id, $startDate, $endDate);
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance), // Revenue is normally credit (negative in our system)
                ];
            });

        // Get expense accounts
        $expenses = Account::where('type', AccountType::EXPENSE)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($startDate, $endDate) {
                $balance = $this->ledgerService->calculateBalance($account->id, $startDate, $endDate);
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance),
                ];
            });

        $totalRevenue = $revenues->sum('balance');
        $totalExpenses = $expenses->sum('balance');
        $netProfit = $totalRevenue - $totalExpenses;

        return view('reports.profit-loss', compact(
            'revenues',
            'expenses',
            'totalRevenue',
            'totalExpenses',
            'netProfit',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Balance Sheet
     * الميزانية العمومية
     */
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : now();

        // Get assets
        $assets = Account::where('type', AccountType::ASSET)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = $this->ledgerService->calculateBalance($account->id, null, $asOfDate);
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $balance,
                ];
            });

        // Get liabilities
        $liabilities = Account::where('type', AccountType::LIABILITY)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = $this->ledgerService->calculateBalance($account->id, null, $asOfDate);
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance),
                ];
            });

        // Get equity
        $equity = Account::where('type', AccountType::EQUITY)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($asOfDate) {
                $balance = $this->ledgerService->calculateBalance($account->id, null, $asOfDate);
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance),
                ];
            });

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        return view('reports.balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'asOfDate'
        ));
    }

    /**
     * Customer Sales Summary Report
     * ملخص مبيعات العملاء
     */
    public function customerSalesSummary(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $customerId = $request->customer_id;

        // Build query
        $query = DB::table('sales_invoices')
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('sales_invoices.deleted_at')
            ->where('sales_invoices.status', '!=', 'cancelled');

        if ($customerId) {
            $query->where('sales_invoices.customer_id', $customerId);
        }

        // Get summary data
        $data = $query
            ->select([
                'customers.id as customer_id',
                'customers.code as customer_code',
                'customers.name as customer_name',
                DB::raw('COUNT(sales_invoices.id) as invoice_count'),
                DB::raw('SUM(sales_invoices.subtotal) as total_subtotal'),
                DB::raw('SUM(sales_invoices.tax_amount) as total_tax'),
                DB::raw('SUM(sales_invoices.discount_amount) as total_discount'),
                DB::raw('SUM(sales_invoices.total) as total_sales'),
                DB::raw('SUM(sales_invoices.amount_paid) as total_paid'),
                DB::raw('SUM(sales_invoices.balance_due) as total_due'),
            ])
            ->groupBy('customers.id', 'customers.code', 'customers.name')
            ->orderByDesc('total_sales')
            ->get();

        // Overall totals
        $totals = [
            'invoice_count' => $data->sum('invoice_count'),
            'total_subtotal' => $data->sum('total_subtotal'),
            'total_tax' => $data->sum('total_tax'),
            'total_discount' => $data->sum('total_discount'),
            'total_sales' => $data->sum('total_sales'),
            'total_paid' => $data->sum('total_paid'),
            'total_due' => $data->sum('total_due'),
        ];

        // Get all customers for filter
        $customers = Customer::active()->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.customer-sales', compact(
            'data',
            'totals',
            'customers',
            'startDate',
            'endDate',
            'customerId'
        ));
    }

    /**
     * Supplier Purchase Summary Report
     * ملخص مشتريات الموردين
     */
    public function supplierPurchaseSummary(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $supplierId = $request->supplier_id;

        // Build query
        $query = DB::table('purchase_invoices')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->whereBetween('purchase_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('purchase_invoices.deleted_at')
            ->where('purchase_invoices.status', '!=', 'cancelled');

        if ($supplierId) {
            $query->where('purchase_invoices.supplier_id', $supplierId);
        }

        // Get summary data
        $data = $query
            ->select([
                'suppliers.id as supplier_id',
                'suppliers.code as supplier_code',
                'suppliers.name as supplier_name',
                DB::raw('COUNT(purchase_invoices.id) as invoice_count'),
                DB::raw('SUM(purchase_invoices.subtotal) as total_subtotal'),
                DB::raw('SUM(purchase_invoices.tax_amount) as total_tax'),
                DB::raw('SUM(purchase_invoices.total) as total_purchases'),
                DB::raw('SUM(purchase_invoices.amount_paid) as total_paid'),
                DB::raw('SUM(purchase_invoices.balance_due) as total_due'),
            ])
            ->groupBy('suppliers.id', 'suppliers.code', 'suppliers.name')
            ->orderByDesc('total_purchases')
            ->get();

        // Overall totals
        $totals = [
            'invoice_count' => $data->sum('invoice_count'),
            'total_subtotal' => $data->sum('total_subtotal'),
            'total_tax' => $data->sum('total_tax'),
            'total_purchases' => $data->sum('total_purchases'),
            'total_paid' => $data->sum('total_paid'),
            'total_due' => $data->sum('total_due'),
        ];

        // Get all suppliers for filter
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('reports.supplier-purchases', compact(
            'data',
            'totals',
            'suppliers',
            'startDate',
            'endDate',
            'supplierId'
        ));
    }

    /**
     * AR Aging Report
     * تقرير أعمار ديون العملاء
     */
    public function arAging(Request $request)
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : now();

        // Get unpaid invoices with customer info
        $invoices = SalesInvoice::with('customer')
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->orderBy('invoice_date')
            ->get();

        // Calculate aging buckets
        $agingData = $invoices->groupBy('customer_id')->map(function ($customerInvoices) use ($asOfDate) {
            $customer = $customerInvoices->first()->customer;

            $buckets = [
                'current' => 0,      // 0-30 days
                'days_31_60' => 0,   // 31-60 days
                'days_61_90' => 0,   // 61-90 days
                'over_90' => 0,      // 90+ days
                'total' => 0,
            ];

            foreach ($customerInvoices as $invoice) {
                $daysPastDue = $asOfDate->diffInDays($invoice->due_date ?? $invoice->invoice_date, false);
                $amount = $invoice->balance_due;

                if ($daysPastDue <= 0) {
                    $buckets['current'] += $amount;
                } elseif ($daysPastDue <= 30) {
                    $buckets['current'] += $amount;
                } elseif ($daysPastDue <= 60) {
                    $buckets['days_31_60'] += $amount;
                } elseif ($daysPastDue <= 90) {
                    $buckets['days_61_90'] += $amount;
                } else {
                    $buckets['over_90'] += $amount;
                }
                $buckets['total'] += $amount;
            }

            return [
                'customer_id' => $customer->id,
                'customer_code' => $customer->code,
                'customer_name' => $customer->name,
                'buckets' => $buckets,
            ];
        })->values();

        // Calculate totals
        $totals = [
            'current' => $agingData->sum('buckets.current'),
            'days_31_60' => $agingData->sum('buckets.days_31_60'),
            'days_61_90' => $agingData->sum('buckets.days_61_90'),
            'over_90' => $agingData->sum('buckets.over_90'),
            'total' => $agingData->sum('buckets.total'),
        ];

        return view('reports.ar-aging', compact('agingData', 'totals', 'asOfDate'));
    }

    /**
     * AP Aging Report
     * تقرير أعمار ديون الموردين
     */
    public function apAging(Request $request)
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : now();

        // Get unpaid invoices with supplier info
        $invoices = PurchaseInvoice::with('supplier')
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'cancelled')
            ->orderBy('invoice_date')
            ->get();

        // Calculate aging buckets
        $agingData = $invoices->groupBy('supplier_id')->map(function ($supplierInvoices) use ($asOfDate) {
            $supplier = $supplierInvoices->first()->supplier;

            $buckets = [
                'current' => 0,
                'days_31_60' => 0,
                'days_61_90' => 0,
                'over_90' => 0,
                'total' => 0,
            ];

            foreach ($supplierInvoices as $invoice) {
                $daysPastDue = $asOfDate->diffInDays($invoice->due_date ?? $invoice->invoice_date, false);
                $amount = $invoice->balance_due;

                if ($daysPastDue <= 0) {
                    $buckets['current'] += $amount;
                } elseif ($daysPastDue <= 30) {
                    $buckets['current'] += $amount;
                } elseif ($daysPastDue <= 60) {
                    $buckets['days_31_60'] += $amount;
                } elseif ($daysPastDue <= 90) {
                    $buckets['days_61_90'] += $amount;
                } else {
                    $buckets['over_90'] += $amount;
                }
                $buckets['total'] += $amount;
            }

            return [
                'supplier_id' => $supplier->id,
                'supplier_code' => $supplier->code,
                'supplier_name' => $supplier->name,
                'buckets' => $buckets,
            ];
        })->values();

        // Calculate totals
        $totals = [
            'current' => $agingData->sum('buckets.current'),
            'days_31_60' => $agingData->sum('buckets.days_31_60'),
            'days_61_90' => $agingData->sum('buckets.days_61_90'),
            'over_90' => $agingData->sum('buckets.over_90'),
            'total' => $agingData->sum('buckets.total'),
        ];

        return view('reports.ap-aging', compact('agingData', 'totals', 'asOfDate'));
    }
}
