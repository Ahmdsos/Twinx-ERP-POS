<?php

namespace Modules\Accounting\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Enums\JournalStatus;
use Modules\Accounting\Exceptions\UnbalancedJournalException;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Core\Contracts\AccountableContract;

/**
 * JournalService - Core service for journal entry operations
 * 
 * This service handles:
 * - Creating journal entries with validation
 * - Posting journal entries to the ledger
 * - Reversing posted entries
 * - Creating entries from AccountableContract implementations
 */
class JournalService
{
    /**
     * Create a new journal entry
     * 
     * @param array $data Entry header data
     * @param array $lines Array of line items [{account_id, debit, credit, description?}]
     * @return JournalEntry
     * @throws UnbalancedJournalException
     */
    public function create(array $data, array $lines): JournalEntry
    {
        // Validate that entry will be balanced
        $this->validateBalance($lines);

        return DB::transaction(function () use ($data, $lines) {
            // Determine fiscal year if not provided
            $entryDate = Carbon::parse($data['entry_date'] ?? now());
            $fiscalYear = FiscalYear::forDate($entryDate);

            // Create the journal entry header
            $entry = JournalEntry::create([
                'entry_date' => $entryDate,
                'fiscal_year_id' => $fiscalYear?->id,
                'reference' => $data['reference'] ?? null,
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => JournalStatus::DRAFT,
                'total_debit' => 0,
                'total_credit' => 0,
            ]);

            // Create the line items
            foreach ($lines as $line) {
                $this->createLine($entry, $line);
            }

            // Refresh to get updated totals
            $entry->refresh();

            return $entry;
        });
    }

    /**
     * Create a journal entry from an AccountableContract implementation
     */
    public function createFromAccountable(AccountableContract $source): JournalEntry
    {
        $lines = $source->getJournalLines();

        return $this->create([
            'entry_date' => $source->getJournalDate(),
            'reference' => $source->getJournalReference(),
            'description' => $source->getJournalDescription(),
            'source_type' => get_class($source),
            'source_id' => $source->getKey(),
        ], $lines);
    }

    /**
     * Add a line to an existing journal entry
     */
    public function addLine(JournalEntry $entry, array $lineData): JournalEntryLine
    {
        if (!$entry->isEditable()) {
            throw new \RuntimeException('Cannot add lines to a posted or reversed entry.');
        }

        return $this->createLine($entry, $lineData);
    }

    /**
     * Post a journal entry (make it affect the ledger)
     */
    public function post(JournalEntry $entry): JournalEntry
    {
        if (!$entry->canBePosted()) {
            if (!$entry->isBalanced()) {
                throw new UnbalancedJournalException(
                    $entry->total_debit,
                    $entry->total_credit
                );
            }
            throw new \RuntimeException('Entry cannot be posted. Check status and line count.');
        }

        return DB::transaction(function () use ($entry) {
            // Update account balances
            $this->updateAccountBalances($entry, 1); // 1 = add to balance

            // Update entry status
            $entry->update([
                'status' => JournalStatus::POSTED,
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);

            return $entry->fresh();
        });
    }

    /**
     * Reverse a posted journal entry
     */
    public function reverse(JournalEntry $entry, ?string $description = null): JournalEntry
    {
        if (!$entry->canBeReversed()) {
            throw new \RuntimeException('Entry cannot be reversed.');
        }

        return DB::transaction(function () use ($entry, $description) {
            // Create reversal entry with swapped debits/credits
            $reversalLines = $entry->lines->map(function ($line) {
                return [
                    'account_id' => $line->account_id,
                    'debit' => $line->credit,  // Swap debit and credit
                    'credit' => $line->debit,
                    'description' => $line->description,
                    'cost_center' => $line->cost_center,
                    'subledger_type' => $line->subledger_type,
                    'subledger_id' => $line->subledger_id,
                ];
            })->toArray();

            $reversalEntry = $this->create([
                'entry_date' => now(),
                'reference' => "REV-{$entry->entry_number}",
                'description' => $description ?? "Reversal of {$entry->entry_number}: {$entry->description}",
            ], $reversalLines);

            // Post the reversal entry
            $this->post($reversalEntry);

            // Mark original entry as reversed
            $entry->update([
                'status' => JournalStatus::REVERSED,
                'reversed_by_entry_id' => $reversalEntry->id,
                'reversed_at' => now(),
            ]);

            return $reversalEntry;
        });
    }

    /**
     * Delete a draft journal entry
     */
    public function delete(JournalEntry $entry): bool
    {
        if (!$entry->isEditable()) {
            throw new \RuntimeException('Cannot delete a posted or reversed entry.');
        }

        return $entry->delete();
    }

    /**
     * Validate that lines balance
     */
    protected function validateBalance(array $lines): void
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($lines as $line) {
            // Truth: Explicitly round each line before summing to match DB storage
            $totalDebit += round((float) ($line['debit'] ?? 0), 2);
            $totalCredit += round((float) ($line['credit'] ?? 0), 2);
        }

        // Standard accounting precision
        $precision = 0.001;

        if (abs($totalDebit - $totalCredit) > $precision) {
            throw new UnbalancedJournalException($totalDebit, $totalCredit);
        }
    }

    /**
     * Create a single journal entry line
     */
    protected function createLine(JournalEntry $entry, array $lineData): JournalEntryLine
    {
        // Validate account exists and is postable
        $account = Account::findOrFail($lineData['account_id']);

        if (!$account->isPostable()) {
            throw new \RuntimeException(
                "Cannot post to account '{$account->name}'. Account is a header or inactive."
            );
        }

        return JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $lineData['account_id'],
            'debit' => round($lineData['debit'] ?? 0, 2),
            'credit' => round($lineData['credit'] ?? 0, 2),
            'description' => $lineData['description'] ?? null,
            'cost_center' => $lineData['cost_center'] ?? null,
            'subledger_type' => $lineData['subledger_type'] ?? null,
            'subledger_id' => $lineData['subledger_id'] ?? null,
        ]);
    }

    /**
     * Update account balances when posting/reversing
     * 
     * @param JournalEntry $entry
     * @param int $multiplier (1 for post, -1 for unpost)
     */
    protected function updateAccountBalances(JournalEntry $entry, int $multiplier): void
    {
        // Group lines by account
        $accountTotals = $entry->lines
            ->groupBy('account_id')
            ->map(function ($lines) use ($multiplier) {
                return [
                    'debit' => $lines->sum('debit') * $multiplier,
                    'credit' => $lines->sum('credit') * $multiplier,
                ];
            });

        foreach ($accountTotals as $accountId => $totals) {
            // Use lockForUpdate() to prevent race conditions during concurrent balance updates
            // This ensures atomic read-modify-write operation on account balance
            $account = Account::lockForUpdate()->find($accountId);
            if ($account) {
                // Calculate new balance based on account type
                if ($account->isDebitNormal()) {
                    $account->balance += ($totals['debit'] - $totals['credit']);
                } else {
                    $account->balance += ($totals['credit'] - $totals['debit']);
                }
                $account->save();
            }
        }
    }
}
