<?php

namespace Modules\HR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HR\Models\Advance;
use Modules\HR\Models\Employee;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Models\Account;
use Modules\Core\Models\Setting;
use Illuminate\Support\Facades\DB;

class AdvanceController extends Controller
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $query = Advance::with('employee');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $advances = $query->latest()->paginate(15)->withQueryString();
        $employees = Employee::where('status', 'active')->get();

        return view('hr::advances.index', compact('advances', 'employees'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('status', 'active')->get();
        $selected_employee_id = $request->employee_id;
        return view('hr::advances.create', compact('employees', 'selected_employee_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'amount' => 'required|numeric|min:1',
            'repayment_month' => 'required|integer|min:1|max:12',
            'repayment_year' => 'required|integer|min:' . date('Y'),
            'notes' => 'nullable|string',
        ]);

        Advance::create([
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'request_date' => now(),
            'repayment_month' => $request->repayment_month,
            'repayment_year' => $request->repayment_year,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return redirect()->route('hr.advances.index')->with('success', 'تم تقديم طلب السلفة بنجاح.');
    }

    public function show(Advance $advance)
    {
        return view('hr::advances.show', compact('advance'));
    }

    public function approve(Advance $advance)
    {
        if ($advance->status !== 'pending') {
            return back()->with('error', 'لا يمكن اعتماد سلفة غير معلقة.');
        }

        $advance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد السلفة.');
    }

    public function pay(Request $request, Advance $advance)
    {
        if ($advance->status !== 'approved') {
            return back()->with('error', 'يجب اعتماد السلفة أولاً قبل الصرف.');
        }

        // Accounting Logic: Pay from Treasury (Credit Asset) -> To Employee Advance (Debit Asset)
        // Wait, giving money DECREASES Treasury (Credit) and INCREASES Employee Loan (Debit Asset).
        // Journal: Dr Employee Advances / Cr Treasury (or Bank)

        try {
            DB::transaction(function () use ($request, $advance) {
                $treasuryAccountCode = Setting::getValue('acc_cash');
                $advancesAccountCode = Setting::getValue('acc_employee_advances');

                if (!$treasuryAccountCode || !$advancesAccountCode) {
                    throw new \Exception("يرجى ضبط حسابات 'الخزينة الرئيسية' و 'سلف العاملين' في الإعدادات أولاً.");
                }

                $treasuryAccount = Account::where('code', $treasuryAccountCode)->firstOrFail();
                $advancesAccount = Account::where('code', $advancesAccountCode)->firstOrFail();

                // Create Journal Entry
                // create(array $data, array $lines)
                $entry = $this->journalService->create([
                    'entry_date' => now(),
                    'reference' => 'ADV-' . $advance->id,
                    'description' => "صرف سلفة للموظف: " . $advance->employee->full_name,
                    'source_type' => Advance::class,
                    'source_id' => $advance->id,
                ], [
                    [
                        'account_id' => $advancesAccount->id,
                        'debit' => $advance->amount,
                        'credit' => 0,
                        'description' => "سلفة موظف: " . $advance->employee->full_name,
                    ],
                    [
                        'account_id' => $treasuryAccount->id,
                        'debit' => 0,
                        'credit' => $advance->amount,
                        'description' => "صرف نقدية - سلفة",
                    ],
                ]);

                $this->journalService->post($entry);

                $advance->update([
                    'status' => 'paid',
                    'paid_by' => auth()->id(),
                    'paid_at' => now(),
                    'journal_entry_id' => $entry->id,
                ]);
            });

            return back()->with('success', 'تم صرف السلفة وتسجيل القيد المحاسبي بنجاح.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
