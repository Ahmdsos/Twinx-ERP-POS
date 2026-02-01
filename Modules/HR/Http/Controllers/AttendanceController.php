<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Attendance;
use Modules\HR\Services\AttendanceService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display a listing of personal/global attendance.
     */
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('attendance_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('attendance_date', '<=', $request->to_date);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')
            ->orderBy('clock_in', 'desc')
            ->paginate(20)
            ->withQueryString();

        $employees = Employee::orderBy('full_name')->get();

        $todayCount = Attendance::whereDate('attendance_date', Carbon::today())
            ->whereIn('status', ['present', 'late'])
            ->count();

        return view('hr::attendance.index', compact('attendances', 'employees', 'todayCount'));
    }

    /**
     * Handle Clock In request.
     */
    public function checkIn(Request $request)
    {
        $employee = null;

        // If user is linked to an employee, use that
        if (auth()->user()->employee) {
            $employee = auth()->user()->employee;
        } elseif ($request->has('employee_id')) {
            // For admin check-ins
            $employee = Employee::findOrFail($request->employee_id);
        }

        if (!$employee) {
            return back()->with('error', 'لا يوجد موظف مرتبط بحسابك لتسجيل الحضور.');
        }

        try {
            $this->attendanceService->checkIn($employee, $request->only('notes'));
            return back()->with('success', 'تم تسجيل الحضور بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle Clock Out request.
     */
    public function checkOut(Request $request)
    {
        $employee = null;

        if (auth()->user()->employee) {
            $employee = auth()->user()->employee;
        } elseif ($request->has('employee_id')) {
            $employee = Employee::findOrFail($request->employee_id);
        }

        if (!$employee) {
            return back()->with('error', 'لا يوجد موظف مرتبط بحسابك لتسجيل الانصراف.');
        }

        try {
            $this->attendanceService->checkOut($employee, $request->only('notes'));
            return back()->with('success', 'تم تسجيل الانصراف بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    /**
     * Store manual attendance record (Admin).
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'attendance_date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required|in:present,absent,late,on_leave',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        try {
            $this->attendanceService->logManualAttendance($employee, $request->all());
            return back()->with('success', 'تم تسجيل الحضور اليدوي بنجاح للموظف ' . $employee->full_name);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
