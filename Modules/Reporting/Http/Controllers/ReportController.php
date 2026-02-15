<?php

namespace Modules\Reporting\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Reporting\Services\FinancialReportService;
use Modules\Reporting\Services\StockReportService;
use Modules\Reporting\Services\SalesReportService;
use Modules\Reporting\Services\PurchaseReportService;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected FinancialReportService $financialReportService;
    protected StockReportService $stockReportService;
    protected SalesReportService $salesReportService;
    protected PurchaseReportService $purchaseReportService;

    public function __construct(
        FinancialReportService $financialReportService,
        StockReportService $stockReportService,
        SalesReportService $salesReportService,
        PurchaseReportService $purchaseReportService
    ) {
        $this->financialReportService = $financialReportService;
        $this->stockReportService = $stockReportService;
        $this->salesReportService = $salesReportService;
        $this->purchaseReportService = $purchaseReportService;
    }

    public function index()
    {
        return view('reports.index');
    }

    public function financial(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->input('type', 'pl'); // pl = Profit Loss, bs = Balance Sheet

        $data = [];
        if ($reportType === 'pl') {
            $modernData = $this->financialReportService->profitAndLoss(
                Carbon::parse($startDate),
                Carbon::parse($endDate)
            );
            // Map to legacy format for back-compatibility with views
            $data = [
                'revenue' => [
                    'total' => $modernData['revenue']['total'],
                    'details' => collect($modernData['revenue']['details'])->map(fn($i) => (object) $i)
                ],
                'expenses' => [
                    'total' => $modernData['expenses']['total'],
                    'details' => collect($modernData['expenses']['details'])->map(fn($i) => (object) $i)
                ],
                'net_profit' => $modernData['summary']['net_income']
            ];
        } else {
            $modernData = $this->financialReportService->balanceSheet(Carbon::parse($endDate));
            // Map to legacy format
            $data = [
                'assets' => collect($modernData['assets']['details'])->map(fn($i) => (object) $i)->groupBy('parent_id'),
                'liabilities' => collect($modernData['liabilities']['details'])->map(fn($i) => (object) $i)->groupBy('parent_id'),
                'equity' => collect($modernData['equity']['details'])->map(fn($i) => (object) $i)->groupBy('parent_id'),
                'totals' => [
                    'assets' => $modernData['assets']['total'],
                    'liabilities' => $modernData['liabilities']['total'],
                    'equity' => $modernData['equity']['total'],
                ]
            ];
        }

        if ($reportType === 'pl') {
            return view('reports.financial.profit-loss', compact('data', 'startDate', 'endDate', 'reportType'));
        }

        return view('reports.financial.balance-sheet', compact('data', 'startDate', 'endDate', 'reportType'));
    }

    public function ledger(Request $request, $id)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $account = \Modules\Accounting\Models\Account::findOrFail($id);

        $data = $this->financialReportService->getAccountLedger(
            $id,
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        );

        return view('reports.financial.ledger', compact('account', 'data', 'startDate', 'endDate'));
    }

    public function salesByProduct(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = collect($this->salesReportService->salesByProduct(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        ))->map(fn($i) => (object) $i);

        return view('reports.sales.by-product', compact('data', 'startDate', 'endDate'));
    }

    public function salesByCustomer(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = collect($this->salesReportService->salesByCustomer(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        ))->map(fn($i) => (object) $i);

        return view('reports.sales.by-customer', compact('data', 'startDate', 'endDate'));
    }

    public function purchasesBySupplier(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = collect($this->purchaseReportService->purchasesBySupplier(
            Carbon::parse($startDate),
            Carbon::parse($endDate)
        ))->map(fn($i) => (object) $i);

        return view('reports.purchases.by-supplier', compact('data', 'startDate', 'endDate'));
    }

    public function inventory()
    {
        $modernData = $this->stockReportService->stockValuation();

        // Map to legacy expected format
        $stockValue = collect($modernData['items'])->map(fn($item) => (object) [
            'id' => $item['product_id'],
            'name' => $item['product_name'],
            'sku' => $item['sku'],
            'category_name' => $item['category_name'],
            'brand_name' => $item['brand_name'],
            'stock_quantity' => $item['quantity'],
            'cost_price' => $item['average_cost'],
            'selling_price' => $item['selling_price'],
            'total_cost_value' => $item['total_value'],
            'total_retail_value' => $item['quantity'] * ($item['average_cost'] * 1.5) // Approximate retail if not tracked
        ]);

        $totalCostValue = $modernData['summary']['total_value'];
        $totalRetailValue = $stockValue->sum('total_retail_value');

        return view('reports.inventory.valuation', compact('stockValue', 'totalCostValue', 'totalRetailValue'));
    }

    public function lowStock()
    {
        $modernData = $this->stockReportService->lowStock();

        // Map to legacy format
        $lowStockItems = collect($modernData['items'])->map(fn($item) => (object) [
            'id' => $item['product_id'],
            'name' => $item['product_name'],
            'sku' => $item['sku'],
            'category_name' => $item['category_name'],
            'brand_name' => $item['brand_name'],
            'current_stock' => $item['current_stock'],
            'reorder_level' => $item['min_level']
        ]);

        return view('reports.inventory.low-stock', compact('lowStockItems'));
    }

    public function shifts(Request $request)
    {
        $query = \App\Models\PosShift::with('user')->latest('opened_at');

        if ($request->filled('cashier_id')) {
            $query->where('user_id', $request->cashier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }

        $shifts = $query->paginate(20)->withQueryString();
        $cashiers = \App\Models\User::whereHas('shifts')->get();

        return view('reports.shifts.index', compact('shifts', 'cashiers'));
    }
}
