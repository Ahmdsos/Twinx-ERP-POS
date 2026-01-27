<?php

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\Supplier;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Accounting\Models\Account;

/**
 * DashboardService - Provides dashboard widgets data
 */
class DashboardService
{
    public function __construct(
        protected FinancialReportService $financialService,
        protected AgingReportService $agingService,
        protected StockReportService $stockService
    ) {
    }

    /**
     * Get complete dashboard data
     */
    public function getDashboard(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'sales' => $this->getSalesWidgets(),
            'purchasing' => $this->getPurchasingWidgets(),
            'inventory' => $this->getInventoryWidgets(),
            'financial' => $this->getFinancialWidgets(),
            'quick_stats' => $this->getQuickStats(),
        ];
    }

    /**
     * Sales widgets
     */
    protected function getSalesWidgets(): array
    {
        $today = now();
        $thisMonth = $today->copy()->startOfMonth();

        // Sales this month
        $salesThisMonth = SalesInvoice::query()
            ->whereDate('invoice_date', '>=', $thisMonth)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // Orders pending
        $pendingOrders = SalesOrder::query()
            ->whereIn('status', ['confirmed', 'processing'])
            ->count();

        // AR Outstanding
        $arOutstanding = SalesInvoice::query()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');

        // Top 5 customers this month
        $topCustomers = SalesInvoice::query()
            ->select('customer_id', DB::raw('SUM(total) as total_sales'))
            ->whereDate('invoice_date', '>=', $thisMonth)
            ->where('status', '!=', 'cancelled')
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->with('customer:id,code,name')
            ->get()
            ->map(fn($i) => [
                'customer_id' => $i->customer_id,
                'customer_name' => $i->customer?->name ?? 'Unknown',
                'total_sales' => round($i->total_sales, 2),
            ]);

        return [
            'sales_this_month' => round($salesThisMonth, 2),
            'pending_orders' => $pendingOrders,
            'ar_outstanding' => round($arOutstanding, 2),
            'top_customers' => $topCustomers,
        ];
    }

    /**
     * Purchasing widgets
     */
    protected function getPurchasingWidgets(): array
    {
        $today = now();
        $thisMonth = $today->copy()->startOfMonth();

        // Purchases this month
        $purchasesThisMonth = PurchaseInvoice::query()
            ->whereDate('invoice_date', '>=', $thisMonth)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // POs pending approval
        $pendingPOs = PurchaseOrder::query()
            ->where('status', 'pending')
            ->count();

        // AP Outstanding
        $apOutstanding = PurchaseInvoice::query()
            ->whereIn('status', ['pending', 'partial'])
            ->sum('balance_due');

        return [
            'purchases_this_month' => round($purchasesThisMonth, 2),
            'pending_purchase_orders' => $pendingPOs,
            'ap_outstanding' => round($apOutstanding, 2),
        ];
    }

    /**
     * Inventory widgets
     */
    protected function getInventoryWidgets(): array
    {
        // Total stock value
        $totalValue = ProductStock::query()
            ->selectRaw('SUM(quantity * average_cost) as total')
            ->value('total') ?? 0;

        // Low stock count
        $lowStock = $this->stockService->lowStock();
        $lowStockCount = $lowStock['summary']['total_alerts'];

        // Active products
        $activeProducts = Product::where('is_active', true)->count();

        return [
            'total_stock_value' => round($totalValue, 2),
            'low_stock_alerts' => $lowStockCount,
            'active_products' => $activeProducts,
        ];
    }

    /**
     * Financial widgets
     */
    protected function getFinancialWidgets(): array
    {
        $today = now();
        $thisMonth = $today->copy()->startOfMonth();
        $lastMonth = $today->copy()->subMonth();

        // This month P&L
        $pnlThisMonth = $this->financialService->profitAndLoss($thisMonth, $today);

        // Cash balance (from cash accounts)
        $cashBalance = $this->getCashBalance();

        return [
            'revenue_this_month' => $pnlThisMonth['revenue']['total'],
            'expenses_this_month' => $pnlThisMonth['expenses']['total'],
            'net_income_this_month' => $pnlThisMonth['summary']['net_income'],
            'cash_balance' => round($cashBalance, 2),
        ];
    }

    /**
     * Quick stats counters
     */
    protected function getQuickStats(): array
    {
        return [
            'total_customers' => Customer::where('is_active', true)->count(),
            'total_suppliers' => Supplier::where('is_active', true)->count(),
            'total_products' => Product::where('is_active', true)->count(),
        ];
    }

    /**
     * Get cash balance from cash/bank accounts
     */
    protected function getCashBalance(): float
    {
        // Cash accounts typically start with 1101, 1102
        $cashAccounts = Account::query()
            ->where('code', 'like', '110%')
            ->orWhere('code', 'like', '111%')
            ->pluck('id');

        if ($cashAccounts->isEmpty()) {
            return 0;
        }

        return DB::table('journal_entry_lines')
            ->whereIn('account_id', $cashAccounts)
            ->whereIn('journal_entry_id', function ($q) {
                $q->select('id')
                    ->from('journal_entries')
                    ->where('status', 'posted');
            })
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;
    }
}
