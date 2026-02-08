<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HRController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stats = [
            'total_employees' => \Modules\HR\Models\Employee::count(),
            'active_employees' => \Modules\HR\Models\Employee::where('status', \Modules\HR\Enums\EmployeeStatus::ACTIVE->value)->count(),
            'total_salary' => \Modules\HR\Models\Employee::where('status', \Modules\HR\Enums\EmployeeStatus::ACTIVE->value)->sum('basic_salary'),
        ];

        // Alerts: Expiring documents in the next 30 days
        $expiringDocuments = \Modules\HR\Models\Document::with('employee')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->get();

        // Pending leaves
        $pendingLeaves = \Modules\HR\Models\Leave::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Load current user's employee profile
        $user = auth()->user();
        $employee = $user ? $user->employee : null;
        $attendanceStatus = 'not_linked';

        if ($employee) {
            $attendanceService = app(\Modules\HR\Services\AttendanceService::class);
            $attendanceStatus = $attendanceService->getTodayStatus($employee);
        }

        return view('hr::index', compact('stats', 'attendanceStatus', 'expiringDocuments', 'pendingLeaves', 'employee'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hr::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('hr::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('hr::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }
}
