<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Payroll;
use Modules\HR\Models\PayrollItem;
use Modules\HR\Services\PayrollService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display a listing of payrolls.
     */
    public function index(Request $request)
    {
        $query = Payroll::query();

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('hr::payroll.index', compact('payrolls'));
    }

    /**
     * Display the specified payroll.
     */
    public function show(Payroll $payroll)
    {
        $payroll->load('items.employee');
        return view('hr::payroll.show', compact('payroll'));
    }

    /**
     * Generate payroll for a specific month.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        try {
            $payroll = $this->payrollService->generateMonthlyPayroll($request->month, $request->year);
            return redirect()->route('hr.payroll.show', $payroll->id)
                ->with('success', 'تم توليد مسيرة الرواتب بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Post payroll to accounting.
     */
    public function post(Payroll $payroll)
    {
        try {
            $this->payrollService->postToAccounting($payroll);
            return back()->with('success', 'تم ترحيل مسيرة الرواتب للحسابات العامة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an individual payroll item (Manual adjustment).
     */
    public function updateItem(Request $request, PayrollItem $item)
    {
        if ($item->payroll->status !== 'draft') {
            return back()->with('error', 'لا يمكن تعديل كشف راتب معتمد أو مرحل.');
        }

        $request->validate([
            'allowances' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $item) {
                $item->update([
                    'allowances' => $request->allowances,
                    'deductions' => $request->deductions,
                    'notes' => $request->notes,
                    'net_salary' => $item->basic_salary + $request->allowances - $request->deductions - $item->advance_deductions,
                ]);

                // Update parent payroll totals
                $payroll = $item->payroll;
                $payroll->update([
                    'total_allowances' => $payroll->items()->sum('allowances'),
                    'total_deductions' => $payroll->items()->sum('deductions'),
                    'total_advance_deductions' => $payroll->items()->sum('advance_deductions'),
                    'net_salary' => $payroll->items()->sum('net_salary'),
                ]);
            });

            return back()->with('success', 'تم تحديث بيانات الموظف ' . $item->employee->full_name . ' بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ أثناء التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Recalculate payroll to sync with attendance changes.
     */
    public function recalculate(Payroll $payroll)
    {
        try {
            $this->payrollService->recalculate($payroll);
            return back()->with('success', 'تم إعادة احتساب كشف الرواتب بنجاح بناءً على سجلات الحضور الحالية.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
