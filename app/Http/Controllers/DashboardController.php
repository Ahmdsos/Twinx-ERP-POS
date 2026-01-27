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

    /**
     * Show the dashboard
     */
    public function index()
    {
        $dashboard = $this->dashboardService->getDashboard();

        return view('dashboard', compact('dashboard'));
    }
}
