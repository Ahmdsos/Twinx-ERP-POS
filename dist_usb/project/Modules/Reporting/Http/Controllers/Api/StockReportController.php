<?php

namespace Modules\Reporting\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Reporting\Services\StockReportService;
use Carbon\Carbon;

/**
 * StockReportController - API for inventory reports
 */
class StockReportController extends Controller
{
    public function __construct(protected StockReportService $service)
    {
    }

    /**
     * Stock Valuation Report
     */
    public function valuation(Request $request): JsonResponse
    {
        $warehouseId = $request->filled('warehouse_id')
            ? (int) $request->warehouse_id
            : null;

        return response()->json($this->service->stockValuation($warehouseId));
    }

    /**
     * Low Stock Alert Report
     */
    public function lowStock(Request $request): JsonResponse
    {
        $warehouseId = $request->filled('warehouse_id')
            ? (int) $request->warehouse_id
            : null;

        return response()->json($this->service->lowStock($warehouseId));
    }

    /**
     * Stock Movement History
     */
    public function movements(Request $request): JsonResponse
    {
        $productId = $request->filled('product_id') ? (int) $request->product_id : null;
        $warehouseId = $request->filled('warehouse_id') ? (int) $request->warehouse_id : null;
        $fromDate = $request->filled('from_date') ? Carbon::parse($request->from_date) : null;
        $toDate = $request->filled('to_date') ? Carbon::parse($request->to_date) : null;
        $limit = $request->integer('limit', 100);

        return response()->json($this->service->movementHistory(
            $productId,
            $warehouseId,
            $fromDate,
            $toDate,
            min($limit, 500)
        ));
    }

    /**
     * Stock Summary by Warehouse
     */
    public function byWarehouse(): JsonResponse
    {
        return response()->json($this->service->stockByWarehouse());
    }
}
