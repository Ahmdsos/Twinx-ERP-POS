<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;

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

        // 2. Real-time Daily Metrics (Operational) - Harmonized with Returns
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $salesToday = $this->dashboardService->getNetSales($todayStart, $todayEnd);
        $netEarningsToday = $this->dashboardService->getNetRevenue($todayStart, $todayEnd);

        $ordersCount = \Modules\Sales\Models\SalesInvoice::whereBetween('invoice_date', [$todayStart, $todayEnd])
            ->where('status', '!=', \Modules\Sales\Enums\SalesInvoiceStatus::CANCELLED)
            ->count();

        // 3. Charts Data (Sales Trend - Last 7 Days) - Harmonized
        $dates = collect();
        $totals = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $dates->push($date->locale('ar')->dayName);
            $totals->push($this->dashboardService->getNetSales($dayStart, $dayEnd));
        }

        // 4. Top Selling Products (This Month) - Harmonized
        $topProducts = \Modules\Sales\Models\SalesInvoiceLine::select(
            'product_id',
            \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_qty'),
            \Illuminate\Support\Facades\DB::raw('SUM(line_total) as total_sales')
        )
            ->whereHas('invoice', function ($q) {
                $q->whereDate('invoice_date', '>=', now()->startOfMonth())
                    ->where('status', '!=', \Modules\Sales\Enums\SalesInvoiceStatus::CANCELLED);
            })
            ->with([
                'product:id,name,sku',
                'product.returns' => function ($q) {
                    $q->whereHas('salesReturn', function ($sq) {
                        $sq->whereDate('return_date', '>=', now()->startOfMonth())
                            ->where('status', \Modules\Sales\Enums\SalesReturnStatus::APPROVED);
                    });
                }
            ])
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get()
            ->map(function ($item) {
                // Use eager-loaded collection to avoid N+1
                $productReturns = $item->product->returns ?? collect();
                $returnQty = $productReturns->sum('quantity');
                $returnSales = $productReturns->sum('line_total');

                return [
                    'name' => $item->product->name ?? 'Unknown',
                    'qty' => $item->total_qty - $returnQty,
                    'sales' => $item->total_sales - $returnSales
                ];
            });

        // 5. Recent Invoices
        $recentInvoices = \Modules\Sales\Models\SalesInvoice::with(['customer'])
            ->latest()
            ->take(6)
            ->get();

        // 6. Financial Fallbacks (Real-time Ledger Calculation - SSOT)
        $revenueVal = 0;
        $arVal = 0;
        $apVal = 0;

        if ($revenueVal == 0) {
            // Revenue (Class 4). Must filter by Journal Status (POSTED/REVERSED)
            $revenueVal = \Modules\Accounting\Models\JournalEntryLine::whereHas('account', function ($q) {
                $q->where('code', 'LIKE', '4%');
            })->whereHas('journalEntry', function ($q) {
                $q->whereIn('status', [
                    \Modules\Accounting\Enums\JournalStatus::POSTED,
                    \Modules\Accounting\Enums\JournalStatus::REVERSED
                ]);
            })->sum(\Illuminate\Support\Facades\DB::raw('credit - debit'));

            $revenueVal = max(0, $revenueVal);
        }

        if ($arVal == 0) {
            $totalInvoiceDebt = \Modules\Sales\Models\SalesInvoice::whereIn('status', [
                \Modules\Sales\Enums\SalesInvoiceStatus::PENDING,
                \Modules\Sales\Enums\SalesInvoiceStatus::PARTIAL
            ])->sum('balance_due');

            $totalPayments = \Modules\Sales\Models\CustomerPayment::sum('amount');
            $totalAllocated = \Illuminate\Support\Facades\DB::table('customer_payment_allocations')->sum('amount');
            $totalUnallocated = max(0, $totalPayments - $totalAllocated);

            $arVal = $totalInvoiceDebt - $totalUnallocated;
        }

        if ($apVal == 0) {
            $apVal = \Modules\Purchasing\Models\PurchaseInvoice::whereIn('status', [
                \Modules\Purchasing\Enums\PurchaseInvoiceStatus::PENDING,
                \Modules\Purchasing\Enums\PurchaseInvoiceStatus::PARTIAL
            ])->sum('balance_due');
        }

        // Construct the View Contract Array
        $dashboard = [
            'sales_today' => $salesToday,
            'net_earnings_today' => $netEarningsToday,
            'orders_count_today' => $ordersCount,
            'cash_on_hand' => $serviceData['financial']['cash_balance'] ?? 0,
            'revenue_mtd' => $revenueVal,
            'receivables' => $arVal,
            'payables' => $apVal,
            'low_stock_count' => $serviceData['inventory']['low_stock_alerts'] ?? 0,
            'inventory_value' => $serviceData['inventory']['total_stock_value'] ?? 0,
            'sales_trend' => [
                'labels' => $dates->toArray(),
                'values' => $totals->toArray()
            ],
            'top_products' => $topProducts,
            'recent_invoices' => $recentInvoices
        ];

        return view('dashboard', compact('dashboard'));
    }
}
