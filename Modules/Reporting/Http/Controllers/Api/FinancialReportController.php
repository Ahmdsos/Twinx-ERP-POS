<?php

namespace Modules\Reporting\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reporting\Services\FinancialReportService;
use Carbon\Carbon;

/**
 * FinancialReportController - API for financial reports
 */
class FinancialReportController extends Controller
{
    public function __construct(protected FinancialReportService $service)
    {
    }

    /**
     * Trial Balance
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : null;

        return response()->json($this->service->trialBalance($asOfDate));
    }

    /**
     * Profit & Loss Statement
     */
    public function profitAndLoss(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        return response()->json($this->service->profitAndLoss($fromDate, $toDate));
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : null;

        return response()->json($this->service->balanceSheet($asOfDate));
    }
}
