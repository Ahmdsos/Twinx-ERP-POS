<?php

namespace App\Http\Controllers;

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
     * Show edit form
     */
    public function edit(Account $account)
    {
        $types = AccountType::cases();
        $parentAccounts = Account::whereNull('parent_id')
            ->where('id', '!=', $account->id)
            ->orderBy('code')
            ->get();

        return view('accounting.accounts.edit', compact('account', 'types', 'parentAccounts'));
    }

    /**
     * Update account
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:accounts,code,' . $account->id,
            'name' => 'required|string|max:255',
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

    /**
     * Delete account
     */
    public function destroy(Account $account)
    {
        if ($account->journalLines()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له قيود');
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'تم حذف الحساب بنجاح');
    }
}
