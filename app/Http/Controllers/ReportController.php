<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Reports\Services\ReportService;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
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
            $data = $this->reportService->getProfitAndLoss($startDate, $endDate);
        } else {
            $data = $this->reportService->getBalanceSheet($endDate);
        }

        return view('reports.financial.profit-loss', compact('data', 'startDate', 'endDate', 'reportType'));
    }

    public function salesByProduct(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getSalesByProduct($startDate, $endDate);

        return view('reports.sales.by-product', compact('data', 'startDate', 'endDate'));
    }

    public function salesByCustomer(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getSalesByCustomer($startDate, $endDate);

        return view('reports.sales.by-customer', compact('data', 'startDate', 'endDate'));
    }

    public function purchasesBySupplier(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getPurchasesBySupplier($startDate, $endDate);

        return view('reports.purchases.by-supplier', compact('data', 'startDate', 'endDate'));
    }

    public function inventory()
    {
        $stockValue = $this->reportService->getStockValuation();

        $totalCostValue = $stockValue->sum('total_cost_value');
        $totalRetailValue = $stockValue->sum('total_retail_value');

        return view('reports.inventory.valuation', compact('stockValue', 'totalCostValue', 'totalRetailValue'));
    }

    public function lowStock()
    {
        $lowStockItems = $this->reportService->getLowStockAlerts();

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
