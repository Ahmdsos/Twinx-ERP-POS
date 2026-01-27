<?php

namespace Modules\Reporting\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reporting\Services\AgingReportService;
use Carbon\Carbon;

/**
 * AgingReportController - API for AR/AP aging reports
 */
class AgingReportController extends Controller
{
    public function __construct(protected AgingReportService $service)
    {
    }

    /**
     * AR Aging Report
     */
    public function arAging(Request $request): JsonResponse
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : null;

        return response()->json($this->service->arAging($asOfDate));
    }

    /**
     * AP Aging Report
     */
    public function apAging(Request $request): JsonResponse
    {
        $asOfDate = $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)
            : null;

        return response()->json($this->service->apAging($asOfDate));
    }
}
