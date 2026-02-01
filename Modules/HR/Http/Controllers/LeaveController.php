<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Leave;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $request->validate([
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        $employee->leaves()->create([
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'تم تقديم طلب الإجازة بنجاح.');
    }

    public function approve(Leave $leave)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($leave) {
            $leave->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            // Automatically create attendance records as "On Leave" for the period
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                \Modules\HR\Models\Attendance::updateOrCreate(
                    [
                        'employee_id' => $leave->employee_id,
                        'attendance_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'status' => \Modules\HR\Models\Attendance::STATUS_ON_LEAVE,
                        'notes' => 'إجازة معتمدة: ' . $leave->leave_type,
                    ]
                );
            }
        });

        return back()->with('success', 'تم الموافقة على طلب الإجازة وتحديث سجل الحضور.');
    }

    public function reject(Leave $leave)
    {
        $leave->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'تم رفض طلب الإجازة.');
    }
}
