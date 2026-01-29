<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;
use Modules\Inventory\Models\Product;
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

    /**
     * Sales By Product Report
     * تقرير المبيعات حسب المنتج
     */
    public function salesByProduct(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        // Get sales data grouped by product
        $data = DB::table('sales_invoice_lines')
            ->join('sales_invoices', 'sales_invoice_lines.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('products', 'sales_invoice_lines.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('sales_invoices.deleted_at')
            ->where('sales_invoices.status', '!=', 'cancelled')
            ->select([
                'products.id as product_id',
                'products.sku',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(sales_invoice_lines.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_lines.subtotal) as total_subtotal'),
                DB::raw('SUM(sales_invoice_lines.tax_amount) as total_tax'),
                DB::raw('SUM(sales_invoice_lines.total) as total_sales'),
                DB::raw('COUNT(DISTINCT sales_invoices.id) as invoice_count'),
            ])
            ->groupBy('products.id', 'products.sku', 'products.name', 'categories.name')
            ->orderByDesc('total_sales')
            ->get();

        // Calculate totals
        $totals = [
            'total_qty' => $data->sum('total_qty'),
            'total_subtotal' => $data->sum('total_subtotal'),
            'total_tax' => $data->sum('total_tax'),
            'total_sales' => $data->sum('total_sales'),
            'invoice_count' => $data->sum('invoice_count'),
        ];

        return view('reports.sales-by-product', compact('data', 'totals', 'startDate', 'endDate'));
    }

    /**
     * Inventory Valuation Report
     * تقرير تقييم المخزون
     */
    public function inventoryValuation(Request $request)
    {
        // Get all products with their stock
        $data = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                $totalStock = $product->getTotalStock();
                $costValue = $totalStock * $product->cost_price;
                $saleValue = $totalStock * $product->selling_price;
                $potentialProfit = $saleValue - $costValue;

                return (object) [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? '-',
                    'unit' => $product->unit?->abbreviation ?? 'PCS',
                    'stock' => $totalStock,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'cost_value' => $costValue,
                    'sale_value' => $saleValue,
                    'potential_profit' => $potentialProfit,
                ];
            })
            ->filter(fn($p) => $p->stock > 0);

        // Calculate totals
        $totals = [
            'total_items' => $data->count(),
            'total_quantity' => $data->sum('stock'),
            'total_cost_value' => $data->sum('cost_value'),
            'total_sale_value' => $data->sum('sale_value'),
            'total_potential_profit' => $data->sum('potential_profit'),
        ];

        return view('reports.inventory-valuation', compact('data', 'totals'));
    }

    /**
     * Profit Margin Analysis Report
     * تقرير تحليل هامش الربح
     */
    public function profitMarginAnalysis(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();

        // Get sales data with cost information
        $data = DB::table('sales_invoice_lines')
            ->join('sales_invoices', 'sales_invoice_lines.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('products', 'sales_invoice_lines.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('sales_invoices.deleted_at')
            ->where('sales_invoices.status', '!=', 'cancelled')
            ->select([
                'products.id as product_id',
                'products.sku',
                'products.name as product_name',
                'products.cost_price',
                'categories.name as category_name',
                DB::raw('SUM(sales_invoice_lines.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_lines.total) as total_revenue'),
                DB::raw('SUM(sales_invoice_lines.quantity * products.cost_price) as total_cost'),
            ])
            ->groupBy('products.id', 'products.sku', 'products.name', 'products.cost_price', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($row) {
                $profit = $row->total_revenue - $row->total_cost;
                $marginPercent = $row->total_revenue > 0
                    ? ($profit / $row->total_revenue) * 100
                    : 0;

                $row->profit = $profit;
                $row->margin_percent = $marginPercent;
                return $row;
            });

        // Calculate totals
        $totals = [
            'total_qty' => $data->sum('total_qty'),
            'total_revenue' => $data->sum('total_revenue'),
            'total_cost' => $data->sum('total_cost'),
            'total_profit' => $data->sum('profit'),
            'avg_margin' => $data->avg('margin_percent'),
        ];

        return view('reports.profit-margin', compact('data', 'totals', 'startDate', 'endDate'));
    }

    /**
     * Export Sales by Product to Excel/CSV
     */
    public function exportSalesByProduct(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? \Carbon\Carbon::parse($request->start_date)
            : now()->startOfMonth();
        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->end_date)
            : now()->endOfMonth();

        $data = DB::table('sales_invoice_lines')
            ->join('sales_invoices', 'sales_invoice_lines.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('products', 'sales_invoice_lines.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('sales_invoices.deleted_at')
            ->where('sales_invoices.status', '!=', 'cancelled')
            ->select([
                'products.sku',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(sales_invoice_lines.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_lines.total) as total_sales'),
            ])
            ->groupBy('products.id', 'products.sku', 'products.name', 'categories.name')
            ->orderByDesc('total_sales')
            ->get();

        $exportService = new \App\Services\ExportService();

        $headers = ['SKU', 'المنتج', 'التصنيف', 'الكمية', 'الإجمالي'];
        $rows = $data->map(fn($r) => [
            $r->sku,
            $r->product_name,
            $r->category_name ?? '-',
            $r->total_qty,
            $r->total_sales,
        ]);

        return $exportService->toExcelCsv($headers, $rows, 'sales-by-product-' . now()->format('Y-m-d') . '.csv');
    }

    /**
     * Export Inventory Valuation to Excel/CSV
     */
    public function exportInventoryValuation()
    {
        $data = Product::with(['category', 'unit'])
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => $p->getTotalStock() > 0)
            ->map(function ($p) {
                $stock = $p->getTotalStock();
                return [
                    $p->sku,
                    $p->name,
                    $p->category?->name ?? '-',
                    $stock,
                    $p->cost_price,
                    $p->selling_price,
                    $stock * $p->cost_price,
                    $stock * $p->selling_price,
                ];
            });

        $exportService = new \App\Services\ExportService();

        $headers = ['SKU', 'المنتج', 'التصنيف', 'الكمية', 'سعر التكلفة', 'سعر البيع', 'قيمة التكلفة', 'قيمة البيع'];

        return $exportService->toExcelCsv($headers, $data, 'inventory-valuation-' . now()->format('Y-m-d') . '.csv');
    }
}

