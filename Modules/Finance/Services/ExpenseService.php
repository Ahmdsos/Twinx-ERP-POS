<?php

namespace Modules\Finance\Services;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Expense;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Enums\JournalStatus;

class ExpenseService
{
    protected JournalService $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Record a new expense and optionally post to GL
     */
    public function recordExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            // 1. Create Expense
            $expense = Expense::create($data);

            // 2. If approved, create and post Journal Entry
            if ($expense->status === 'approved') {
                $this->createJournalEntry($expense);
            }

            return $expense;
        });
    }

    /**
     * Create Journal Entry for an Expense
     */
    public function createJournalEntry(Expense $expense): void
    {
        if ($expense->journal_entry_id) {
            return; // Already has an entry
        }

        $expenseCategory = $expense->category;

        if (!$expenseCategory || !$expenseCategory->account_id) {
            \Log::error("Expense Recording Warning: Category '{$expenseCategory?->name}' has no linked account. Journal Entry skipped for Expense #{$expense->reference_number}");
            return; // Skip accounting, but keep the record
        }

        if (!$expense->payment_account_id) {
            throw new \Exception("Expense has no payment account (Credit source).");
        }

        // Journal Lines
        $lines = [
            [
                'account_id' => $expenseCategory->account_id,
                'debit' => $expense->amount,
                'credit' => 0,
                'description' => $expense->notes ?? "Expense: {$expense->reference_number}",
            ],
            [
                'account_id' => $expense->payment_account_id,
                'debit' => 0,
                'credit' => $expense->amount,
                'description' => $expense->notes ?? "Expense: {$expense->reference_number}",
            ]
        ];

        // Create Entry
        $entry = $this->journalService->create([
            'entry_date' => $expense->expense_date,
            'reference' => $expense->reference_number,
            'description' => $expense->notes,
            'source_type' => Expense::class,
            'source_id' => $expense->id,
        ], $lines);

        // Link Expense to Entry
        $expense->update(['journal_entry_id' => $entry->id]);

        // Post immediately if expense is approved
        if ($expense->status === 'approved') {
            $this->journalService->post($entry);
        }
    }
}
