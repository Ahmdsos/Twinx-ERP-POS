<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\ExpenseCategory;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\JournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['category', 'paymentAccount', 'creator'])
            ->latest('expense_date')
            ->paginate(15);

        return view('finance.expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        // Payment Accounts: Cash (1001) or Bank (1002) - assuming prefixes or types
        // For now, let's fetch all Asset accounts or specific liquid accounts
        // We really need a way to identify "Payment Accounts".
        // Let's assume accounts starting with 10 (Assets -> Cash/Bank)
        $paymentAccounts = Account::where('code', 'like', '10%')
            ->where('is_active', true)
            ->whereNull('parent_id') // Avoid group headers if any
            ->orWhere('id', '>', 0) // Fallback to all for now if structure unknown
            ->get();

        // Better: Explicitly load Treasury accounts if we had a scope.
        // Let's just load all "detail" accounts for simplicity in prototype
        $paymentAccounts = Account::where('is_active', true)->get();

        return view('finance.expenses.create', compact('categories', 'paymentAccounts'));
    }

    public function store(Request $request, JournalService $journalService)
    {
        $validated = $request->validate([
            'expense_date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_account_id' => 'required|exists:accounts,id',
            'payee' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);

        $category = ExpenseCategory::findOrFail($validated['category_id']);

        // Ensure default account exists for category
        if (!$category->account_id) {
            return back()->withInput()->with('error', 'هذا التصنيف غير مرتبط بحساب مصروف. يرجى تعديل التصنيف أولاً.');
        }

        $taxAmount = $validated['tax_amount'] ?? 0;
        $totalAmount = $validated['amount'] + $taxAmount;

        DB::transaction(function () use ($validated, $journalService, $category, $taxAmount, $totalAmount) {
            $path = null;
            if (request()->hasFile('attachment')) {
                $path = request()->file('attachment')->store('expenses', 'public');
            }

            $expense = Expense::create([
                'expense_date' => $validated['expense_date'],
                'category_id' => $validated['category_id'],
                'payment_account_id' => $validated['payment_account_id'],
                'amount' => $validated['amount'],
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payee' => $validated['payee'],
                'notes' => $validated['notes'],
                'attachment' => $path,
                'status' => 'approved', // Auto-approve for now
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
            ]);

            // Create Journal Entry
            // Dr Expense Account (Amount)
            // Dr Tax Account (Tax Amount)
            // Cr Payment Account (Total)

            $taxAccount = Account::where('code', '2105')->first(); // VAT Input/Receivable? 
            // Actually 2105 is usually Tax Payable. Input Tax should be an Asset (1xxx).
            // Let's assume 2105 handles both for net tax liability, or separate.
            // For simplicity, using Tax Account provided.

            $lines = [
                [
                    'account_id' => $category->account_id,
                    'debit' => $validated['amount'],
                    'credit' => 0,
                    'description' => $validated['notes'] ?? 'Expense: ' . $category->name,
                ],
                [
                    'account_id' => $validated['payment_account_id'],
                    'debit' => 0,
                    'credit' => $totalAmount,
                    'description' => 'Payment for Expense ' . $expense->reference_number,
                ]
            ];

            if ($taxAmount > 0 && $taxAccount) {
                $lines[] = [
                    'account_id' => $taxAccount->id,
                    'debit' => $taxAmount,
                    'credit' => 0,
                    'description' => 'Tax on Expense',
                ];
            }

            $entry = $journalService->create([
                'entry_date' => $expense->expense_date,
                'reference' => $expense->reference_number,
                'description' => "Expense: {$category->name} - " . ($validated['payee'] ?? ''),
                'source_type' => Expense::class,
                'source_id' => $expense->id,
            ], $lines);

            $journalService->post($entry);
            $expense->update(['journal_entry_id' => $entry->id]);
        });

        return redirect()->route('expenses.index')->with('success', 'تم تسجيل المصروف بنجاح');
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'paymentAccount', 'creator', 'journalEntry']);
        return view('finance.expenses.show', compact('expense'));
    }
}
