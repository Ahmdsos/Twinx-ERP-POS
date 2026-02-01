<?php

namespace Modules\Reports\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;
use Modules\Inventory\Models\Product;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get Profit and Loss (Income Statement)
     * Revenue - Expenses = Net Profit
     */
    public function getProfitAndLoss($startDate, $endDate)
    {
        // Get all balances effectively
        $revenueDetails = $this->getDetailedBalances(AccountType::REVENUE, $startDate, $endDate);
        $expenseDetails = $this->getDetailedBalances(AccountType::EXPENSE, $startDate, $endDate);

        // Calculate Totals from Details
        // Note: period_balance is already signed correctly (Cr-Dr or Dr-Cr)
        $totalRevenue = $revenueDetails->sum('period_balance');
        $totalExpenses = $expenseDetails->sum('period_balance');

        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'revenue' => [
                'total' => $totalRevenue,
                'details' => $revenueDetails
            ],
            'expenses' => [
                'total' => $totalExpenses,
                'details' => $expenseDetails
            ],
            'net_profit' => $netProfit
        ];
    }

    /**
     * Get Balance Sheet
     * Assets = Liabilities + Equity
     */
    public function getBalanceSheet($asOfDate)
    {
        // Balance sheet requires cumulative balance up to the date
        // For simplicity in this iteration, we treat it as "current" if date is today,
        // or we should calculate historical.
        // Given constraints, we'll calculate logic:

        // Assets (Dr - Cr)
        $assets = $this->getDetailedBalances(AccountType::ASSET, '1900-01-01', $asOfDate);

        // Liabilities (Cr - Dr)
        $liabilities = $this->getDetailedBalances(AccountType::LIABILITY, '1900-01-01', $asOfDate);

        // Equity (Cr - Dr)
        $equity = $this->getDetailedBalances(AccountType::EQUITY, '1900-01-01', $asOfDate);

        return [
            'assets' => $assets->groupBy('parent_id'),
            'liabilities' => $liabilities->groupBy('parent_id'),
            'equity' => $equity->groupBy('parent_id'),
            'totals' => [
                'assets' => $assets->sum('period_balance'),
                'liabilities' => $liabilities->sum('period_balance'),
                'equity' => $equity->sum('period_balance'),
            ]
        ];
    }

    /**
     * Get Stock Valuation
     * Qty * Cost (using ProductStock table)
     */
    public function getStockValuation()
    {
        return DB::table('products')
            ->leftJoin('product_stock', 'products.id', '=', 'product_stock.product_id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'categories.name as category_name',
                'brands.name as brand_name',
                // Calculate Weighted Average Cost from Stock
                DB::raw('COALESCE(SUM(product_stock.total_cost) / NULLIF(SUM(product_stock.quantity), 0), products.cost_price) as cost_price'),
                'products.selling_price',
                DB::raw('COALESCE(SUM(product_stock.quantity), 0) as stock_quantity'),
                DB::raw('COALESCE(SUM(product_stock.total_cost), 0) as total_cost_value'),
                DB::raw('COALESCE(SUM(product_stock.quantity) * products.selling_price, 0) as total_retail_value')
            )
            ->whereNull('products.deleted_at')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.cost_price',
                'products.selling_price',
                'categories.name',
                'brands.name'
            )
            ->get();
    }

    /**
     * Get Low Stock Alerts
     * Rule: Any product with Stock <= 5 (Hardcoded threshold as per user request)
     */
    public function getLowStockAlerts()
    {
        return DB::table('products')
            ->leftJoin('product_stock', 'products.id', '=', 'product_stock.product_id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.reorder_level',
                'categories.name as category_name',
                'brands.name as brand_name',
                DB::raw('COALESCE(SUM(product_stock.quantity), 0) as current_stock')
            )
            ->whereNull('products.deleted_at')
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'products.reorder_level',
                'categories.name',
                'brands.name'
            )
            // User Rule: Alert if Stock <= 5 (regardless of reorder_level)
            ->havingRaw('COALESCE(SUM(product_stock.quantity), 0) <= 5')
            ->orderBy('current_stock', 'asc')
            ->get();
    }

    /**
     * Get Sales Analysis by Product
     */
    public function getSalesByProduct($startDate, $endDate)
    {
        return DB::table('sales_invoice_lines')
            ->join('sales_invoices', 'sales_invoice_lines.sales_invoice_id', '=', 'sales_invoices.id')
            ->join('products', 'sales_invoice_lines.product_id', '=', 'products.id')
            ->select(
                'products.name as product_name',
                'products.sku',
                DB::raw('SUM(sales_invoice_lines.quantity) as total_qty'),
                DB::raw('SUM(sales_invoice_lines.line_total) as total_sales')
            )
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereIn('sales_invoices.status', ['paid', 'partial', 'pending']) // Include pending to show all debts
            ->whereNull('sales_invoices.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sales')
            ->get();
    }

    /**
     * Get Sales Analysis by Customer
     */
    public function getSalesByCustomer($startDate, $endDate)
    {
        return DB::table('sales_invoices')
            ->join('customers', 'sales_invoices.customer_id', '=', 'customers.id')
            ->select(
                'customers.name as customer_name',
                'customers.phone',
                DB::raw('COUNT(sales_invoices.id) as invoice_count'),
                DB::raw('SUM(sales_invoices.total) as total_sales'),
                DB::raw('SUM(sales_invoices.balance_due) as total_due')
            )
            ->whereBetween('sales_invoices.invoice_date', [$startDate, $endDate])
            ->whereIn('sales_invoices.status', ['paid', 'partial', 'pending'])
            ->whereNull('sales_invoices.deleted_at')
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc('total_sales')
            ->get();
    }

    /**
     * Get Purchase Analysis by Supplier
     */
    public function getPurchasesBySupplier($startDate, $endDate)
    {
        return DB::table('purchase_invoices')
            ->join('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.name as supplier_name',
                DB::raw('COUNT(purchase_invoices.id) as invoice_count'),
                DB::raw('SUM(purchase_invoices.total) as total_purchases'),
                DB::raw('SUM(purchase_invoices.balance_due) as total_due')
            )
            ->whereBetween('purchase_invoices.invoice_date', [$startDate, $endDate])
            ->whereNull('purchase_invoices.deleted_at')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_purchases')
            ->get();
    }

    /**
     * Optimized Detailed Balances Calculation
     * Solves N+1 Query Problem and ensures correct Drift/Credit logic
     */
    private function getDetailedBalances(AccountType $type, $start, $end)
    {
        // 1. Get all active accounts of this type (exclude headers)
        $accounts = Account::where('type', $type->value)
            ->where('is_header', false)
            ->get();

        if ($accounts->isEmpty()) {
            return collect();
        }

        $accountIds = $accounts->pluck('id')->toArray();

        // 2. Aggregate Journal Lines in ONE query
        // Ensure End Date covers the full day
        $endDateTime = Carbon::parse($end)->endOfDay();

        $balances = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereIn('journal_entry_lines.account_id', $accountIds)
            ->whereBetween('journal_entries.entry_date', [$start, $endDateTime])
            ->where('journal_entries.status', 'posted')
            ->whereNull('journal_entries.deleted_at')
            ->select(
                'journal_entry_lines.account_id',
                DB::raw('SUM(debit) as total_debit'),
                DB::raw('SUM(credit) as total_credit')
            )
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        // 3. Map correct balance direction (Dr-Cr or Cr-Dr)
        return $accounts->map(function ($account) use ($balances, $type) {
            $record = $balances->get($account->id);
            $debit = $record ? $record->total_debit : 0;
            $credit = $record ? $record->total_credit : 0;

            if ($type->debitIncreases()) {
                // Asset, Expense: Debit is +
                $balance = $debit - $credit;
            } else {
                // Liability, Equity, Revenue: Credit is +
                $balance = $credit - $debit;
            }

            $account->period_balance = $balance;
            return $account;
        })->filter(function ($account) {
            // Filter non-zero balances (epsilon for float precision)
            return abs($account->period_balance) > 0.001;
        })->values(); // Reset keys
    }
}
