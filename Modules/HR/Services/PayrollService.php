<?php

namespace Modules\HR\Services;

use Modules\HR\Models\Employee;
use Modules\HR\Models\Payroll;
use Modules\HR\Models\PayrollItem;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\JournalService;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollService
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Generate payroll for all active employees for a specific month/year.
     */
    public function generateMonthlyPayroll(int $month, int $year)
    {
        return DB::transaction(function () use ($month, $year) {
            // 1. Accounting Safety Check
            $expAccountCode = Setting::getValue('acc_salaries_exp');
            $payableAccountCode = Setting::getValue('acc_salaries_payable');

            if (!$expAccountCode || !$payableAccountCode) {
                throw new \Exception("إعدادات الحسابات غير مكتملة. يرجى ضبط حساب مصروف الرواتب وحساب الرواتب المستحقة في الإعدادات أولاً.");
            }

            // Check if payroll already exists
            $existing = Payroll::where('month', $month)->where('year', $year)->first();
            if ($existing) {
                throw new \Exception("كشف الرواتب لشهر $month-$year موجود بالفعل.");
            }

            $employees = Employee::where('status', 'active')->get();

            if ($employees->isEmpty()) {
                throw new \Exception("لا يوجد موظفين نشطين لتوليد كشف رواتب لهم.");
            }

            $payroll = Payroll::create([
                'month' => $month,
                'year' => $year,
                'process_date' => now(),
                'net_salary' => 0,
                'total_basic' => 0,
                'status' => 'draft',
                'processed_by' => auth()->id(),
            ]);

            $grandTotal = 0;
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $daysInMonth = $monthStart->daysInMonth;

            foreach ($employees as $employee) {
                $baseSalary = $employee->basic_salary;
                $dailyRate = $baseSalary / $daysInMonth;
                $calculatedDeductions = 0;
                $calculatedAllowances = 0;

                // 1. Attendance Audit: Calculate absences
                // Only count as absent if it's a working day and NO attendance/leave is recorded.
                // For now, we rely on the Attendance model's STATUS_ABSENT records.
                $absentCount = \Modules\HR\Models\Attendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$monthStart, $monthEnd])
                    ->where('status', \Modules\HR\Models\Attendance::STATUS_ABSENT)
                    ->count();

                $calculatedDeductions += ($absentCount * $dailyRate);

                // 2. Future: Link with automated allowances or other modules
                // For now, we initialize as 0 and allow manual override in the UI.

                $netSalary = max(0, $baseSalary + $calculatedAllowances - $calculatedDeductions);

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $baseSalary,
                    'allowances' => $calculatedAllowances,
                    'deductions' => $calculatedDeductions,
                    'net_salary' => $netSalary,
                    'notes' => $absentCount > 0 ? "خصم غياب $absentCount يوم" : null,
                ]);

                $grandTotal += $netSalary;
            }

            $payroll->update([
                'total_basic' => $employees->sum('basic_salary'),
                'total_allowances' => $payroll->items()->sum('allowances'),
                'total_deductions' => $payroll->items()->sum('deductions'),
                'net_salary' => $grandTotal,
            ]);

            return $payroll;
        });
    }

    /**
     * Recalculate a specific payroll (must be draft).
     * Useful if attendance or employee data changed after generation.
     */
    public function recalculate(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            throw new \Exception("لا يمكن إعادة احتساب كشف رواتب معتمد أو مرحل.");
        }

        return DB::transaction(function () use ($payroll) {
            // Delete existing items
            $payroll->items()->delete();

            $year = $payroll->year;
            $month = $payroll->month;

            $employees = Employee::where('status', 'active')->get();
            $grandTotal = 0;
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $daysInMonth = $monthStart->daysInMonth;

            foreach ($employees as $employee) {
                $baseSalary = $employee->basic_salary;
                $dailyRate = $baseSalary / $daysInMonth;
                $calculatedDeductions = 0;
                $calculatedAllowances = 0;

                // 1. Attendance Audit
                $absentCount = \Modules\HR\Models\Attendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$monthStart, $monthEnd])
                    ->where('status', \Modules\HR\Models\Attendance::STATUS_ABSENT)
                    ->count();

                // Track Leaves just for info (Paid leaves don't deduct)
                $leaveCount = \Modules\HR\Models\Attendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$monthStart, $monthEnd])
                    ->where('status', 'on_leave') // Using string as constant might be missing in some contexts
                    ->count();

                $calculatedDeductions += ($absentCount * $dailyRate);

                $netSalary = max(0, $baseSalary + $calculatedAllowances - $calculatedDeductions);

                $notes = [];
                if ($absentCount > 0)
                    $notes[] = "خصم غياب $absentCount يوم";
                if ($leaveCount > 0)
                    $notes[] = "إجازة $leaveCount يوم";

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $baseSalary,
                    'allowances' => $calculatedAllowances,
                    'deductions' => $calculatedDeductions,
                    'net_salary' => $netSalary,
                    'notes' => count($notes) > 0 ? implode(' | ', $notes) : null,
                ]);

                $grandTotal += $netSalary;
            }

            $payroll->update([
                'total_basic' => $employees->sum('basic_salary'),
                'total_allowances' => $payroll->items()->sum('allowances'),
                'total_deductions' => $payroll->items()->sum('deductions'),
                'net_salary' => $grandTotal,
            ]);

            return $payroll;
        });
    }

    /**
     * Post payroll to the accounting ledger.
     */
    public function postToAccounting(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            throw new \Exception("لا يمكن ترحيل كشف رواتب غير مسودة.");
        }

        return DB::transaction(function () use ($payroll) {
            // Validate accounts before posting
            $payroll->getJournalLines();

            // Standardized creation from AccountableContract
            $entry = $this->journalService->createFromAccountable($payroll);

            // Post the entry to affect ledger
            $this->journalService->post($entry);

            $payroll->update([
                'status' => 'processed',
                'journal_entry_id' => $entry->id,
            ]);

            return $payroll;
        });
    }
}
