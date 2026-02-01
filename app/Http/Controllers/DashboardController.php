<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Reporting\Services\DashboardService;

/**
 * DashboardController - Main dashboard for web UI
 */
class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
    }

    public function index()
    {
        // 1. Dashboard Service Data (Aggregated Metrics)
        $serviceData = $this->dashboardService->getDashboard();

        // 2. Real-time Daily Metrics (Operational)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $salesToday = \Modules\Sales\Models\SalesInvoice::whereBetween('invoice_date', [$todayStart, $todayEnd])
            ->where('status', '!=', \Modules\Sales\Enums\SalesInvoiceStatus::CANCELLED)
            ->sum('total');

        $ordersCount = \Modules\Sales\Models\SalesInvoice::whereBetween('invoice_date', [$todayStart, $todayEnd])
            ->count();

        // 3. Charts Data (Sales Trend - Last 7 Days)
        $dates = collect();
        $totals = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dates->push($date->locale('ar')->dayName); // Arabic day name

            $dayTotal = \Modules\Sales\Models\SalesInvoice::whereDate('invoice_date', $date)
                ->where('status', '!=', \Modules\Sales\Enums\SalesInvoiceStatus::CANCELLED)
                ->sum('total');
            $totals->push($dayTotal);
        }

        // 4. Top Selling Products (This Month)
        $topProducts = \Modules\Sales\Models\SalesInvoiceLine::select(
            'product_id',
            \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'),
            \Illuminate\Support\Facades\DB::raw('SUM(line_total) as total_sales')
        )
            ->whereHas('invoice', function ($q) {
                $q->whereDate('invoice_date', '>=', now()->startOfMonth());
            })
            ->with('product:id,name,sku') // Eager load minimal fields
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product->name ?? 'Unknown',
                    'qty' => $item->total_qty,
                    'sales' => $item->total_sales
                ];
            });

        // 5. Recent Invoices
        $recentInvoices = \Modules\Sales\Models\SalesInvoice::with(['customer'])
            ->latest()
            ->take(6)
            ->get();

        // 6. Financial Fallbacks (Real-time Ledger Calculation - SSOT)
        // Since Account->balance is cached and might be 0, we calculate from the Journal Lines directly.

        $revenueVal = 0; // $serviceData['sales']['month_sales'] ?? 0;
        $arVal = 0; // $serviceData['sales']['ar_outstanding'] ?? 0;
        $apVal = 0; // $serviceData['purchasing']['ap_outstanding'] ?? 0;

        if ($revenueVal == 0) {
            // Revenue (Class 4) is Credit normal. Sum Credit - Debit.
            $revenueVal = \Modules\Accounting\Models\JournalEntryLine::whereHas('account', function ($q) {
                $q->where('code', 'LIKE', '4%');
            })->sum(\Illuminate\Support\Facades\DB::raw('credit - debit'));

            // If negative (e.g. returns exceeding sales), show 0 or actual. Revenue usually appears positive.
            $revenueVal = max(0, $revenueVal);
        }

        if ($arVal == 0) {
            // AR: Sum of all customer outstanding balances (Invoices - Unallocated Payments)

            // 1. Total Debt from Invoices
            $totalInvoiceDebt = \Modules\Sales\Models\SalesInvoice::whereIn('status', [
                \Modules\Sales\Enums\SalesInvoiceStatus::PENDING,
                \Modules\Sales\Enums\SalesInvoiceStatus::PARTIAL
            ])->sum('balance_due');

            // 2. Total Unallocated Payments (Credit on Account)
            // Total Payments - Total Allocated
            $totalPayments = \Modules\Sales\Models\CustomerPayment::sum('amount');
            $totalAllocated = \Illuminate\Support\Facades\DB::table('customer_payment_allocations')->sum('amount');

            // Log for debugging if needed (remove in prod)
            // \Log::info("Dashboard AR Debug: Debt=$totalInvoiceDebt, Pay=$totalPayments, Alloc=$totalAllocated");

            $totalUnallocated = max(0, $totalPayments - $totalAllocated);

            $arVal = $totalInvoiceDebt - $totalUnallocated;
        }

        if ($apVal == 0) {
            // AP: Sum of all supplier outstanding balances (Invoices - Unallocated Payments)

            // 1. Total Debt from Purchase Invoices
            $totalPurchaseDebt = \Modules\Purchasing\Models\PurchaseInvoice::whereIn('status', [
                \Modules\Purchasing\Enums\PurchaseInvoiceStatus::PENDING,
                \Modules\Purchasing\Enums\PurchaseInvoiceStatus::PARTIAL
            ])->sum('balance_due');

            // 2. Adjust with unallocated payments if necessary
            // For now, using direct invoice sum as it's the primary source of truth for the dashboard widget
            $apVal = $totalPurchaseDebt;
        }

        // Construct the View Contract Array
        $dashboard = [
            // Operational
            'sales_today' => $salesToday,
            'orders_count_today' => $ordersCount,
            'cash_on_hand' => $serviceData['financial']['cash_balance'] ?? 0,

            // Financial
            'revenue_mtd' => $revenueVal,
            'receivables' => $arVal,
            'payables' => $apVal,

            // Inventory
            'low_stock_count' => $serviceData['inventory']['low_stock_alerts'] ?? 0,
            'inventory_value' => $serviceData['inventory']['total_stock_value'] ?? 0,

            // Charts
            'sales_trend' => [
                'labels' => $dates->toArray(),
                'values' => $totals->toArray()
            ],
            'top_products' => $topProducts,

            // Lists
            'recent_invoices' => $recentInvoices
        ];

        return view('dashboard', compact('dashboard'));
    }
}
