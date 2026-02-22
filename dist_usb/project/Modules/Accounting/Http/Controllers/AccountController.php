<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;

/**
 * AccountController - Chart of Accounts web UI
 */
class AccountController extends Controller
{
    /**
     * List all accounts
     */
    public function index(Request $request)
    {
        $query = Account::query()->orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->paginate(25);
        $types = AccountType::cases();

        return view('accounting.accounts.index', compact('accounts', 'types'));
    }

    /**
     * Show accounts as tree view grouped by type
     */
    public function tree()
    {
        $allAccounts = Account::orderBy('code')->get();

        // Group by type
        $accountsByType = [];
        $typeLabels = [];

        foreach (AccountType::cases() as $type) {
            $typeLabels[$type->value] = $type->label();
            $accountsByType[$type->value] = [];
        }

        // Build tree structure
        $accountsById = $allAccounts->keyBy('id');
        $rootAccounts = $allAccounts->whereNull('parent_id');

        foreach ($rootAccounts as $account) {
            $accountsByType[$account->type->value][] = $this->buildAccountTree($account, $accountsById);
        }

        return view('accounting.accounts.tree', compact('accountsByType', 'typeLabels'));
    }

    /**
     * Recursively build account tree structure
     */
    private function buildAccountTree(Account $account, $accountsById): array
    {
        $children = $accountsById->where('parent_id', $account->id)->values();

        $node = [
            'id' => $account->id,
            'code' => $account->code,
            'name' => $account->name,
            'name_ar' => $account->name_ar,
            'type' => $account->type->value,
            'balance' => $account->balance ?? 0,
            'is_active' => $account->is_active,
            'children' => [],
        ];

        foreach ($children as $child) {
            $node['children'][] = $this->buildAccountTree($child, $accountsById);
        }

        return $node;
    }

    /**
     * Show create form
     */
    public function create()
    {
        $types = AccountType::cases();
        $parentAccounts = Account::whereNull('parent_id')->orderBy('code')->get();

        return view('accounting.accounts.create', compact('types', 'parentAccounts'));
    }

    /**
     * Store new account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'type' => 'required|string',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['type'] = AccountType::from($validated['type']);

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'تم إنشاء الحساب بنجاح');
    }

    /**
     * Show account ledger (transaction history)
     */
    public function show(Request $request, Account $account)
    {
        // Date range filter - default to current month
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Parse dates for query (end of day for end date)
        $queryEndDate = \Carbon\Carbon::parse($endDate)->endOfDay();

        // Get journal entry lines for this account
        $query = $account->journalLines()
            ->with(['journalEntry'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $queryEndDate) {
                $q->whereIn('status', [\Modules\Accounting\Enums\JournalStatus::POSTED])
                    ->whereBetween('entry_date', [$startDate, $queryEndDate]);
            })
            ->orderBy('created_at');

        $transactions = $query->get();

        // Calculate running balance
        $runningBalance = 0;
        $openingBalance = $this->calculateOpeningBalance($account, $startDate);
        $runningBalance = $openingBalance;

        $ledgerEntries = $transactions->map(function ($line) use (&$runningBalance, $account) {
            if (in_array($account->type->value, ['asset', 'expense'])) {
                $runningBalance += $line->debit - $line->credit;
            } else {
                $runningBalance += $line->credit - $line->debit;
            }

            return [
                'date' => $line->journalEntry->entry_date,
                'entry_number' => $line->journalEntry->entry_number,
                'description' => $line->description ?? $line->journalEntry->description,
                'reference' => $line->journalEntry->reference,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'balance' => $runningBalance,
                'journal_entry_id' => $line->journal_entry_id,
            ];
        });

        $totalDebit = $transactions->sum('debit');
        $totalCredit = $transactions->sum('credit');
        $closingBalance = $runningBalance;

        return view('accounting.accounts.show', compact(
            'account',
            'ledgerEntries',
            'openingBalance',
            'closingBalance',
            'totalDebit',
            'totalCredit',
            'startDate',
            'endDate'
        ));
    }

    private function calculateOpeningBalance(Account $account, string $startDate): float
    {
        $lines = $account->journalLines()
            ->whereHas('journalEntry', function ($q) use ($startDate) {
                $q->whereIn('status', [\Modules\Accounting\Enums\JournalStatus::POSTED])
                    ->where('entry_date', '<', $startDate);
            })
            ->get();

        $balance = 0;
        foreach ($lines as $line) {
            if (in_array($account->type->value, ['asset', 'expense'])) {
                $balance += $line->debit - $line->credit;
            } else {
                $balance += $line->credit - $line->debit;
            }
        }

        return $balance;
    }

    public function edit(Account $account)
    {
        $types = AccountType::cases();
        $parentAccounts = Account::whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('accounting.accounts.edit', compact('account', 'types', 'parentAccounts'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'type' => 'required|string',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['type'] = AccountType::from($validated['type']);

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'تم تحديث الحساب بنجاح');
    }

    public function destroy(Account $account)
    {
        try {
            $account->delete();
            return redirect()->route('accounts.index')
                ->with('success', 'تم حذف الحساب بنجاح');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف الحساب');
        }
    }
}
