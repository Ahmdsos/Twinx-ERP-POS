<?php

namespace Modules\Reporting\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Enums\AccountType;
use Modules\Accounting\Enums\JournalStatus;

/**
 * FinancialReportService - Generates financial statements
 */
class FinancialReportService
{
    /**
     * Generate Trial Balance
     * Shows debit/credit balances for all accounts
     */
    public function trialBalance(?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();

        $accounts = Account::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $data = [];
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as $account) {
            $balance = $this->getAccountBalance($account->id, null, $asOfDate);

            if ($balance == 0)
                continue; // Skip zero balances

            $debit = $balance > 0 ? $balance : 0;
            $credit = $balance < 0 ? abs($balance) : 0;

            // Adjust for account type (CR accounts show positive as credit)
            if (in_array($account->type, [AccountType::LIABILITY, AccountType::EQUITY, AccountType::REVENUE])) {
                $debit = $balance < 0 ? abs($balance) : 0;
                $credit = $balance > 0 ? $balance : 0;
            }

            $data[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type->value,
                'debit' => round($debit, 2),
                'credit' => round($credit, 2),
            ];

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        return [
            'as_of_date' => $asOfDate->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'accounts' => $data,
            'totals' => [
                'debit' => round($totalDebit, 2),
                'credit' => round($totalCredit, 2),
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ],
        ];
    }

    /**
     * Generate Profit & Loss Statement
     */
    public function profitAndLoss(Carbon $fromDate, Carbon $toDate): array
    {
        // Revenue accounts (4xxx)
        $revenue = $this->getAccountTypeTotal(AccountType::REVENUE, $fromDate, $toDate);

        // Expense accounts (5xxx, 6xxx)
        $totalExpenses = $this->getAccountTypeTotal(AccountType::EXPENSE, $fromDate, $toDate);

        // Calculate COGS (Accounts starting with 50 or 51)
        // This is a heuristic based on standard chart of accounts
        $cogs = $this->getAccountPrefixTotal(AccountType::EXPENSE, ['50', '51'], $fromDate, $toDate);

        $operatingExpenses = $totalExpenses - $cogs;

        // Get detailed breakdown
        $revenueDetails = $this->getAccountTypeBreakdown(AccountType::REVENUE, $fromDate, $toDate);
        $expenseDetails = $this->getAccountTypeBreakdown(AccountType::EXPENSE, $fromDate, $toDate);

        $grossProfit = $revenue - $cogs;
        $netIncome = $revenue - $totalExpenses;

        return [
            'period' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'generated_at' => now()->toIso8601String(),
            'revenue' => [
                'total' => round($revenue, 2),
                'details' => $revenueDetails,
            ],
            'cogs' => [
                'total' => round($cogs, 2),
            ],
            'operating_expenses' => [
                'total' => round($operatingExpenses, 2),
            ],
            'expenses' => [
                'total' => round($totalExpenses, 2),
                'details' => $expenseDetails, // Detailed list
            ],
            'summary' => [
                'gross_profit' => round($grossProfit, 2),
                'net_income' => round($netIncome, 2),
                'profit_margin' => $revenue > 0 ? round(($netIncome / $revenue) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now();

        // Assets (1xxx)
        $assets = $this->getAccountTypeBalance(AccountType::ASSET, $asOfDate);
        $assetDetails = $this->getAccountTypeBreakdownBalance(AccountType::ASSET, $asOfDate);

        // Liabilities (2xxx)
        $liabilities = $this->getAccountTypeBalance(AccountType::LIABILITY, $asOfDate);
        $liabilityDetails = $this->getAccountTypeBreakdownBalance(AccountType::LIABILITY, $asOfDate);

        // Equity (3xxx)
        $equity = $this->getAccountTypeBalance(AccountType::EQUITY, $asOfDate);
        $equityDetails = $this->getAccountTypeBreakdownBalance(AccountType::EQUITY, $asOfDate);

        // Calculate retained earnings (Revenue - Expenses from beginning)
        $retainedEarnings = $this->calculateRetainedEarnings($asOfDate);
        $totalEquity = $equity + $retainedEarnings;

        return [
            'as_of_date' => $asOfDate->toDateString(),
            'generated_at' => now()->toIso8601String(),
            'assets' => [
                'total' => round($assets, 2),
                'details' => $assetDetails,
            ],
            'liabilities' => [
                'total' => round($liabilities, 2),
                'details' => $liabilityDetails,
            ],
            'equity' => [
                'capital' => round($equity, 2),
                'retained_earnings' => round($retainedEarnings, 2),
                'total' => round($totalEquity, 2),
                'details' => $equityDetails,
            ],
            'summary' => [
                'total_assets' => round($assets, 2),
                'total_liabilities_equity' => round($liabilities + $totalEquity, 2),
                'is_balanced' => abs($assets - ($liabilities + $totalEquity)) < 0.01,
            ],
        ];
    }

    /**
     * Get detailed ledger for an account
     */
    public function getAccountLedger(int $accountId, Carbon $fromDate, Carbon $toDate): array
    {
        // 1. Calculate Opening Balance (Everything before fromDate)
        $openingBalance = $this->getAccountBalance($accountId, null, $fromDate->copy()->subDay());

        // 2. Get Transaction Lines
        $lines = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->whereIn('status', [JournalStatus::POSTED, JournalStatus::REVERSED])
                    ->whereDate('entry_date', '>=', $fromDate)
                    ->whereDate('entry_date', '<=', $toDate);
            })
            ->with(['journalEntry']) // Eager load parent
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.created_at')
            ->select('journal_entry_lines.*') // Avoid column collisions
            ->get();

        return [
            'opening_balance' => $openingBalance,
            'lines' => $lines
        ];
    }

    /**
     * Get account balance as of date
     */
    protected function getAccountBalance(int $accountId, ?Carbon $fromDate = null, ?Carbon $toDate = null): float
    {
        $query = JournalEntryLine::query()
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->whereIn('status', [JournalStatus::POSTED, JournalStatus::REVERSED]);
                if ($fromDate) {
                    $q->whereDate('entry_date', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->whereDate('entry_date', '<=', $toDate);
                }
            });

        $debits = (clone $query)->sum('debit');
        $credits = (clone $query)->sum('credit');

        return $debits - $credits;
    }

    /**
     * Get total for account type with specific code prefixes
     */
    protected function getAccountPrefixTotal(AccountType $type, array $prefixes, Carbon $fromDate, Carbon $toDate): float
    {
        $accountIds = Account::where('type', $type)
            ->where(function ($q) use ($prefixes) {
                foreach ($prefixes as $prefix) {
                    $q->orWhere('code', 'like', $prefix . '%');
                }
            })
            ->pluck('id');

        $total = 0;
        foreach ($accountIds as $accountId) {
            $total += abs($this->getAccountBalance($accountId, $fromDate, $toDate));
        }

        return $total;
    }

    /**
     * Get total for account type in period
     */
    protected function getAccountTypeTotal(AccountType $type, Carbon $fromDate, Carbon $toDate): float
    {
        $accountIds = Account::where('type', $type)->pluck('id');

        $total = 0;
        foreach ($accountIds as $accountId) {
            $balance = $this->getAccountBalance($accountId, $fromDate, $toDate);
            // For Revenue, credits are positive. For Expenses, debits are positive.
            if ($type === AccountType::REVENUE) {
                $total += abs($balance);
            } else {
                $total += abs($balance);
            }
        }

        return $total;
    }

    /**
     * Get account type balance as of date
     */
    protected function getAccountTypeBalance(AccountType $type, Carbon $asOfDate): float
    {
        $accountIds = Account::where('type', $type)->pluck('id');

        $total = 0;
        foreach ($accountIds as $accountId) {
            $total += abs($this->getAccountBalance($accountId, null, $asOfDate));
        }

        return $total;
    }

    /**
     * Get breakdown by account for a type in period
     */
    protected function getAccountTypeBreakdown(AccountType $type, Carbon $fromDate, Carbon $toDate): array
    {
        $accounts = Account::where('type', $type)->orderBy('code')->get();

        $breakdown = [];
        foreach ($accounts as $account) {
            $balance = abs($this->getAccountBalance($account->id, $fromDate, $toDate));
            if ($balance > 0) {
                $breakdown[] = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'period_balance' => round($balance, 2),
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Get breakdown by account for balance
     */
    protected function getAccountTypeBreakdownBalance(AccountType $type, Carbon $asOfDate): array
    {
        $accounts = Account::where('type', $type)->orderBy('code')->get();

        $breakdown = [];
        foreach ($accounts as $account) {
            $balance = abs($this->getAccountBalance($account->id, null, $asOfDate));
            if ($balance > 0) {
                $breakdown[] = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'period_balance' => round($balance, 2),
                ];
            }
        }

        return $breakdown;
    }

    /**
     * Calculate retained earnings
     */
    protected function calculateRetainedEarnings(Carbon $asOfDate): float
    {
        $revenue = $this->getAccountTypeBalance(AccountType::REVENUE, $asOfDate);
        $expenses = $this->getAccountTypeBalance(AccountType::EXPENSE, $asOfDate);

        return $revenue - $expenses;
    }
}
