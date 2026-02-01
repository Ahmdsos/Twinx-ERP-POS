<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Attendance;
use Modules\HR\Models\PayrollItem;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the report selection page.
     */
    public function index()
    {
        $employees = Employee::orderBy('first_name')->get();
        return view('hr::reports.index', compact('employees'));
    }

    /**
     * Generate the comprehensive report.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'employee_id' => 'nullable|exists:hr_employees,id',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        $employeeId = $request->employee_id;

        $employeesQuery = Employee::query();
        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }
        $employees = $employeesQuery->get();

        $reportData = [];

        foreach ($employees as $employee) {
            // Attendance Summary
            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereBetween('attendance_date', [$fromDate, $toDate])
                ->get();

            $attendanceStats = [
                'present' => $attendance->where('status', Attendance::STATUS_PRESENT)->count(),
                'absent' => $attendance->where('status', Attendance::STATUS_ABSENT)->count(),
                'late' => $attendance->where('status', Attendance::STATUS_LATE)->count(),
                'on_leave' => $attendance->where('status', Attendance::STATUS_ON_LEAVE)->count(),
                'total_minutes' => $attendance->sum('duration_minutes'),
            ];

            // Payroll Summary
            $payrolls = PayrollItem::where('employee_id', $employee->id)
                ->whereHas('payroll', function ($q) use ($fromDate, $toDate) {
                    // This is a bit tricky since payroll is monthly. 
                    // We'll consider payrolls whose month/year falls within the range.
                    $q->where(function ($sub) use ($fromDate, $toDate) {
                        $startMonth = (int) $fromDate->format('m');
                        $startYear = (int) $fromDate->format('Y');
                        $endMonth = (int) $toDate->format('m');
                        $endYear = (int) $toDate->format('Y');

                        $sub->whereBetween('year', [$startYear, $endYear]);
                        // Simple check, might need refinement for complex ranges
                    });
                })->get();

            $payrollStats = [
                'total_basic' => $payrolls->sum('basic_salary'),
                'total_allowances' => $payrolls->sum('allowances'),
                'total_deductions' => $payrolls->sum('deductions'),
                'net_salary' => $payrolls->sum('net_salary'),
            ];

            $reportData[] = [
                'employee' => $employee,
                'attendance' => $attendanceStats,
                'payroll' => $payrollStats,
            ];
        }

        return view('hr::reports.employee_summary', [
            'reportData' => $reportData,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'isIndividual' => (bool) $employeeId,
        ]);
    }
}
