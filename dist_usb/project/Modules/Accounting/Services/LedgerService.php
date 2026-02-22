<?php

namespace Modules\Accounting\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Enums\AccountType;
use Modules\Accounting\Enums\JournalStatus;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntryLine;

/**
 * LedgerService - Service for querying account balances and ledger data
 * 
 * This service handles:
 * - Account balance queries
 * - Trial balance generation
 * - Account transaction history
 * - Balance calculations for date ranges
 */
class LedgerService
{
    /**
     * Get the current balance of an account
     */
    public function getAccountBalance(int $accountId): float
    {
        $account = Account::findOrFail($accountId);
        return $account->balance;
    }

    /**
     * Calculate the balance of an account for a specific date range
     * from posted journal entries only
     */
    public function calculateBalance(
        int $accountId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): array {
        $query = JournalEntryLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', JournalStatus::POSTED);

                if ($startDate) {
                    $q->where('entry_date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('entry_date', '<=', $endDate);
                }
            });

        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');

        $account = Account::find($accountId);

        // Calculate balance based on account type
        if ($account && $account->isDebitNormal()) {
            $balance = $totalDebit - $totalCredit;
        } else {
            $balance = $totalCredit - $totalDebit;
        }

        return [
            'account_id' => $accountId,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'balance' => round($balance, 2),
            'start_date' => $startDate?->toDateString(),
            'end_date' => $endDate?->toDateString(),
        ];
    }

    /**
     * Get trial balance (all accounts with their balances)
     */
    public function getTrialBalance(?Carbon $asOfDate = null): Collection
    {
        $asOfDate = $asOfDate ?? now();

        // Get all accounts with their calculated balances
        $accounts = Account::query()
            ->active()
            ->orderBy('code')
            ->get();

        return $accounts->map(function ($account) use ($asOfDate) {
            $balance = $this->calculateBalance($account->id, null, $asOfDate);

            return [
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type->value,
                'type_label' => $account->type->label(),
                'is_header' => $account->is_header,
                'debit_balance' => $account->isDebitNormal() && $balance['balance'] > 0
                    ? $balance['balance'] : 0,
                'credit_balance' => !$account->isDebitNormal() && $balance['balance'] > 0
                    ? $balance['balance'] :
                    ($account->isDebitNormal() && $balance['balance'] < 0
                        ? abs($balance['balance']) : 0),
            ];
        });
    }

    /**
     * Get trial balance summary totals
     */
    public function getTrialBalanceTotals(?Carbon $asOfDate = null): array
    {
        $trialBalance = $this->getTrialBalance($asOfDate);

        return [
            'total_debit' => round($trialBalance->sum('debit_balance'), 2),
            'total_credit' => round($trialBalance->sum('credit_balance'), 2),
            'is_balanced' => abs(
                $trialBalance->sum('debit_balance') - $trialBalance->sum('credit_balance')
            ) < 0.01,
            'as_of_date' => ($asOfDate ?? now())->toDateString(),
        ];
    }

    /**
     * Get account transaction history (ledger detail)
     */
    public function getAccountLedger(
        int $accountId,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        int $limit = 100
    ): Collection {
        $query = JournalEntryLine::query()
            ->with(['journalEntry:id,entry_number,entry_date,reference,description,status'])
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', JournalStatus::POSTED);

                if ($startDate) {
                    $q->where('entry_date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('entry_date', '<=', $endDate);
                }
            })
            ->orderBy(
                JournalEntryLine::query()
                    ->select('entry_date')
                    ->from('journal_entries')
                    ->whereColumn('journal_entries.id', 'journal_entry_lines.journal_entry_id')
                    ->limit(1)
            )
            ->limit($limit);

        $account = Account::find($accountId);
        $runningBalance = 0;

        return $query->get()->map(function ($line) use ($account, &$runningBalance) {
            // Calculate running balance
            if ($account->isDebitNormal()) {
                $runningBalance += ($line->debit - $line->credit);
            } else {
                $runningBalance += ($line->credit - $line->debit);
            }

            return [
                'date' => $line->journalEntry->entry_date->toDateString(),
                'entry_number' => $line->journalEntry->entry_number,
                'reference' => $line->journalEntry->reference,
                'description' => $line->description ?? $line->journalEntry->description,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'running_balance' => round($runningBalance, 2),
            ];
        });
    }

    /**
     * Get balances grouped by account type
     */
    public function getBalancesByType(?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();

        $results = [];

        foreach (AccountType::cases() as $type) {
            $accounts = Account::query()
                ->where('type', $type)
                ->active()
                ->postable()
                ->get();

            $totalBalance = 0;

            foreach ($accounts as $account) {
                $balance = $this->calculateBalance($account->id, null, $asOfDate);
                $totalBalance += $balance['balance'];
            }

            $results[$type->value] = [
                'type' => $type->value,
                'label' => $type->label(),
                'balance' => round($totalBalance, 2),
                'normal_balance' => $type->normalBalance(),
            ];
        }

        return $results;
    }

    /**
     * Get Profit and Loss calculation
     */
    public function getProfitAndLoss(Carbon $startDate, Carbon $endDate): array
    {
        $revenue = $this->getTypeBalance(AccountType::REVENUE, $startDate, $endDate);
        $expenses = $this->getTypeBalance(AccountType::EXPENSE, $startDate, $endDate);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_income' => round($revenue - $expenses, 2),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    /**
     * Get total balance for an account type
     */
    protected function getTypeBalance(
        AccountType $type,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): float {
        $accounts = Account::query()
            ->where('type', $type)
            ->active()
            ->postable()
            ->pluck('id');

        if ($accounts->isEmpty()) {
            return 0;
        }

        $query = JournalEntryLine::query()
            ->whereIn('account_id', $accounts)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', JournalStatus::POSTED);

                if ($startDate) {
                    $q->where('entry_date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('entry_date', '<=', $endDate);
                }
            });

        $totalDebit = $query->sum('debit');
        $totalCredit = $query->sum('credit');

        // Revenue is credit-normal, Expenses are debit-normal
        if ($type === AccountType::REVENUE) {
            return round($totalDebit - $totalCredit, 2);
        } elseif ($type === AccountType::EXPENSE) {
            return round($totalDebit - $totalCredit, 2);
        }

        return 0; // Default return if type is neither revenue nor expense
    }

    /**
     * Get the calculated balance from the ledger (Single Source of Truth)
     */
    public function getCalculatedBalance(int $accountId, ?Carbon $asOfDate = null): float
    {
        $result = $this->calculateBalance($accountId, null, $asOfDate);
        return (float) ($result['balance'] ?? 0);
    }

    /**
     * Verify the integrity of a single account's stored balance
     * @return array [is_valid, stored_balance, calculated_balance, delta]
     */
    public function verifyAccountIntegrity(int $accountId): array
    {
        $account = Account::findOrFail($accountId);
        $calculatedResult = $this->calculateBalance($accountId);

        $storedBalance = (float) $account->balance;
        $calculatedBalance = (float) $calculatedResult['balance'];

        $isValid = abs($storedBalance - $calculatedBalance) < 0.001;

        return [
            'is_valid' => $isValid,
            'stored_balance' => $storedBalance,
            'calculated_balance' => $calculatedBalance,
            'delta' => round($calculatedBalance - $storedBalance, 2)
        ];
    }
}
