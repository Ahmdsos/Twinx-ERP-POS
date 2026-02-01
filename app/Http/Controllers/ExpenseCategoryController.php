<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Accounting\Models\Account;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::with('account')->latest()->paginate(20);
        return view('finance.categories.index', compact('categories'));
    }

    public function create()
    {
        // Fetch Expense Accounts (Expenses - 5xxx)
        $accounts = Account::where('code', 'like', '5%')
            ->where('is_active', true)
            ->get();

        return view('finance.categories.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:expense_categories,code',
            'description' => 'nullable|string',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'تم إضافة بند المصروف بنجاح');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $accounts = Account::where('code', 'like', '5%')
            ->where('is_active', true)
            ->get();
        return view('finance.categories.edit', compact('expenseCategory', 'accounts'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:expense_categories,code,' . $expenseCategory->id,
            'description' => 'nullable|string',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'تم تحديث البيانات بنجاح');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();
        return back()->with('success', 'تم حذف البند بنجاح');
    }
}
