<?php

namespace Modules\HR\Services;

use Modules\HR\Models\Employee;
use Modules\HR\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Check in an employee for the day.
     */
    public function checkIn(Employee $employee, array $data = [])
    {
        $today = Carbon::today();

        // Check if already checked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if ($existing && $existing->clock_in) {
            throw new \Exception('الموظف قام بتسجيل الحضور بالفعل اليوم.');
        }

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $today],
            [
                'clock_in' => Carbon::now()->toTimeString(),
                'status' => \Modules\HR\Enums\AttendanceStatus::PRESENT,
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    /**
     * Check out an employee for the day.
     */
    public function checkOut(Employee $employee, array $data = [])
    {
        $today = Carbon::today();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            throw new \Exception('لم يتم تسجيل حضور للموظف اليوم ليتمكن من تسجيل الانصراف.');
        }

        if ($attendance->clock_out) {
            throw new \Exception('تم تسجيل الانصراف بالفعل اليوم.');
        }

        /** @var Attendance $attendance */
        $attendance->update([
            'clock_out' => Carbon::now()->toTimeString(),
            'notes' => $data['notes'] ?? $attendance->notes,
        ]);

        return $attendance;
    }

    /**
     * Get the current status of an employee for today.
     */
    public function getTodayStatus(Employee $employee)
    {
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if (!$attendance)
            return 'not_checked_in';
        if (!$attendance->clock_out)
            return 'checked_in';
        return 'checked_out';
    }

    /**
     * Manually log attendance for an employee (Admin override).
     */
    public function logManualAttendance(Employee $employee, array $data)
    {
        $date = Carbon::parse($data['attendance_date'])->format('Y-m-d');

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => $date],
            [
                'clock_in' => $data['clock_in'] ?? null,
                'clock_out' => $data['clock_out'] ?? null,
                'status' => $data['status'] ?? \Modules\HR\Enums\AttendanceStatus::PRESENT,
                'notes' => '[سجل يدوي] ' . ($data['notes'] ?? ''),
            ]
        );
    }
}
