<?php

namespace Modules\HR\Services;

use Modules\HR\Models\Employee;
use Modules\HR\Models\Payroll;
use Modules\HR\Models\PayrollItem;
use Modules\HR\Models\Advance;
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

                // 2. Advances Deduction
                // Fetch advances due this month that are APPROVED and NOT YET PAID/DEDUCTED
                // Actually, status should be 'paid' (money given to employee) before we can deduct it?
                // Yes, only 'paid' advances are valid debts. 'deducted' means it's already paid back.
                $advances = \Modules\HR\Models\Advance::where('employee_id', $employee->id)
                    ->where('status', 'paid')
                    ->where('repayment_month', $month)
                    ->where('repayment_year', $year)
                    ->get();

                $advanceDeductionAmount = $advances->sum('amount');
                // Note: we track this separately in the DB for accounting, but for net salary calc it's a deduction

                // 3. Future: Link with automated allowances or other modules
                // For now, we initialize as 0 and allow manual override in the UI.

                // Calculate distributable salary (Before Advance Deduction)
                $distributableSalary = max(0, $baseSalary + $calculatedAllowances - $calculatedDeductions);

                $targetAdvanceDeduction = $advances->sum('amount');

                // Cap the deduction at what is available
                $actualAdvanceDeduction = min($targetAdvanceDeduction, $distributableSalary);

                $netSalary = $distributableSalary - $actualAdvanceDeduction;

                $notes = [];
                if ($absentCount > 0)
                    $notes[] = "خصم غياب $absentCount يوم";
                if ($targetAdvanceDeduction > 0) {
                    if ($actualAdvanceDeduction < $targetAdvanceDeduction) {
                        $notes[] = "خصم سلفة (جزئي): " . number_format($actualAdvanceDeduction, 2) . " من أصل " . number_format($targetAdvanceDeduction, 2);
                    } else {
                        $notes[] = "خصم سلفة: " . number_format($actualAdvanceDeduction, 2);
                    }
                }

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $baseSalary,
                    'allowances' => $calculatedAllowances,
                    'deductions' => $calculatedDeductions,
                    'advance_deductions' => $actualAdvanceDeduction,
                    'net_salary' => $netSalary,
                    'notes' => count($notes) > 0 ? implode(' | ', $notes) : null,
                ]);
            }

            // Force reload items
            $payroll->load('items');

            $payroll->update([
                'total_basic' => $payroll->items->sum('basic_salary'),
                'total_allowances' => $payroll->items->sum('allowances'),
                'total_deductions' => $payroll->items->sum('deductions'),
                'total_advance_deductions' => $payroll->items->sum('advance_deductions'),
                'net_salary' => $payroll->items->sum('net_salary'),
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

                // 2. Advances Deduction (Re-fetch to be safe or keep existing if not paid?)
                // Better to re-fetch to ensure data consistency with current state of advances
                $advances = \Modules\HR\Models\Advance::where('employee_id', $employee->id)
                    ->where('status', 'paid')
                    ->where('repayment_month', $month)
                    ->where('repayment_year', $year)
                    ->get();

                $targetAdvanceDeduction = $advances->sum('amount');

                // Calculate distributable salary (Before Advance Deduction)
                $distributableSalary = max(0, $baseSalary + $calculatedAllowances - $calculatedDeductions);

                // Cap the deduction at what is available
                $actualAdvanceDeduction = min($targetAdvanceDeduction, $distributableSalary);

                $netSalary = $distributableSalary - $actualAdvanceDeduction;

                $notes = [];
                if ($absentCount > 0)
                    $notes[] = "خصم غياب $absentCount يوم";
                if ($leaveCount > 0)
                    $notes[] = "إجازة $leaveCount يوم";
                if ($targetAdvanceDeduction > 0) {
                    if ($actualAdvanceDeduction < $targetAdvanceDeduction) {
                        $notes[] = "خصم سلفة (جزئي): " . number_format($actualAdvanceDeduction, 2) . " من أصل " . number_format($targetAdvanceDeduction, 2);
                    } else {
                        $notes[] = "خصم سلفة: " . number_format($actualAdvanceDeduction, 2);
                    }
                }

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $baseSalary,
                    'allowances' => $calculatedAllowances,
                    'deductions' => $calculatedDeductions,
                    'advance_deductions' => $actualAdvanceDeduction,
                    'net_salary' => $netSalary,
                    'notes' => count($notes) > 0 ? implode(' | ', $notes) : null,
                ]);
            }

            // Force reload of relationship to ensure we capture all created items
            $payroll->load('items');

            $payroll->update([
                'total_basic' => $payroll->items->sum('basic_salary'),
                'total_allowances' => $payroll->items->sum('allowances'),
                'total_deductions' => $payroll->items->sum('deductions'),
                'total_advance_deductions' => $payroll->items->sum('advance_deductions'),
                'net_salary' => $payroll->items->sum('net_salary'),
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
