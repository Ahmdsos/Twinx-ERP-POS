<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Enums\JournalStatus;

/**
 * JournalEntryController - قيود اليومية
 */
class JournalEntryController extends Controller
{
    public function __construct(
        protected JournalService $journalService
    ) {
    }

    /**
     * Display a listing of journal entries
     */
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines.account']);

        // Status filter
        if ($request->filled('status')) {
            $status = JournalStatus::tryFrom($request->status);
            if ($status) {
                $query->where('status', $status);
            }
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('entry_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('entry_date', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $entries = $query->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Stats
        $stats = [
            'total' => JournalEntry::count(),
            'draft' => JournalEntry::draft()->count(),
            'posted' => JournalEntry::posted()->count(),
            'total_debit' => JournalEntry::posted()->sum('total_debit'),
        ];

        return view('accounting.journal-entries.index', compact('entries', 'stats'));
    }

    /**
     * Show the form for creating a new journal entry
     */
    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return view('accounting.journal-entries.create', compact('accounts'));
    }

    /**
     * Store a newly created journal entry
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        // Validate at least one line has debit and one has credit
        $lines = collect($validated['lines'])->filter(function ($line) {
            return ($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0;
        })->values()->all();

        if (count($lines) < 2) {
            return back()->withErrors(['lines' => 'يجب إدخال سطرين على الأقل'])->withInput();
        }

        try {
            $entry = $this->journalService->create([
                'entry_date' => $validated['entry_date'],
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
            ], $lines);

            return redirect()->route('journal-entries.show', $entry)
                ->with('success', 'تم إنشاء قيد اليومية بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['balance' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified journal entry
     */
    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['lines.account', 'postedByUser']);

        return view('accounting.journal-entries.show', compact('journalEntry'));
    }

    /**
     * Show the form for editing the journal entry
     */
    public function edit(JournalEntry $journalEntry)
    {
        if (!$journalEntry->isEditable()) {
            return back()->with('error', 'لا يمكن تعديل قيد تم ترحيله');
        }

        $journalEntry->load('lines.account');

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return view('accounting.journal-entries.edit', compact('journalEntry', 'accounts'));
    }

    /**
     * Update the specified journal entry
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        if (!$journalEntry->isEditable()) {
            return back()->with('error', 'لا يمكن تعديل قيد تم ترحيله');
        }

        $validated = $request->validate([
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        try {
            // Update header
            $journalEntry->update([
                'entry_date' => $validated['entry_date'],
                'reference' => $validated['reference'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            // Delete old lines and create new ones
            $journalEntry->lines()->delete();

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($validated['lines'] as $line) {
                $debit = $line['debit'] ?? 0;
                $credit = $line['credit'] ?? 0;

                if ($debit > 0 || $credit > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $journalEntry->id,
                        'account_id' => $line['account_id'],
                        'debit' => $debit,
                        'credit' => $credit,
                        'description' => $line['description'] ?? null,
                    ]);
                    $totalDebit += $debit;
                    $totalCredit += $credit;
                }
            }

            // Validate balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \RuntimeException('القيد غير متوازن: الدائن لا يساوي المدين');
            }

            $journalEntry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            return redirect()->route('journal-entries.show', $journalEntry)
                ->with('success', 'تم تحديث قيد اليومية بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['balance' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified journal entry
     */
    public function destroy(JournalEntry $journalEntry)
    {
        if (!$journalEntry->isEditable()) {
            return back()->with('error', 'لا يمكن حذف قيد تم ترحيله');
        }

        $this->journalService->delete($journalEntry);

        return redirect()->route('journal-entries.index')
            ->with('success', 'تم حذف قيد اليومية بنجاح');
    }

    /**
     * Post a journal entry
     */
    public function post(JournalEntry $journalEntry)
    {
        if (!$journalEntry->canBePosted()) {
            return back()->with('error', 'لا يمكن ترحيل هذا القيد - تأكد من أنه متوازن ويحتوي على سطرين على الأقل');
        }

        try {
            $this->journalService->post($journalEntry);
            return back()->with('success', 'تم ترحيل القيد بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reverse a posted journal entry
     */
    public function reverse(Request $request, JournalEntry $journalEntry)
    {
        if (!$journalEntry->canBeversed()) {
            return back()->with('error', 'لا يمكن عكس هذا القيد');
        }

        try {
            $reversalEntry = $this->journalService->reverse(
                $journalEntry,
                $request->input('description', 'عكس قيد: ' . $journalEntry->entry_number)
            );
            return redirect()->route('journal-entries.show', $reversalEntry)
                ->with('success', 'تم عكس القيد وإنشاء قيد عكسي');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
