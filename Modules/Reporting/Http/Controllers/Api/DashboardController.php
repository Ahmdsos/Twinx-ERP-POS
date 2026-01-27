<?php

namespace Modules\Reporting\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Reporting\Services\DashboardService;

/**
 * DashboardController - API for dashboard widgets
 */
class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service)
    {
    }

    /**
     * Get complete dashboard data
     */
    public function index(): JsonResponse
    {
        return response()->json($this->service->getDashboard());
    }
}
