<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Enums\JournalStatus;

class ReconcileAccountingBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:reconcile {--fix : Actually update account balances in the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit account balances against posted journal entries. Use --fix to repair discrepancies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fix = $this->option('fix');
        $this->info('🚀 Starting Accounting Audit & Reconciliation...');

        if (!$fix) {
            $this->comment('ℹ️ Running in AUDIT MODE (Dry Run). No changes will be made to the database.');
        } else {
            $this->warn('⚠️ Running in FIX MODE. Account balances will be updated to match the ledger sum.');
        }

        $accounts = Account::postable()->get();
        $bar = $this->output->createProgressBar($accounts->count());

        $bar->start();

        $discrepanciesCount = 0;

        DB::transaction(function () use ($accounts, $bar, $fix, &$discrepanciesCount) {
            foreach ($accounts as $account) {
                // Sum only from POSTED journal entries
                $totals = DB::table('journal_entry_lines')
                    ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->where('journal_entry_lines.account_id', $account->id)
                    ->where('journal_entries.status', JournalStatus::POSTED->value)
                    ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                    ->first();

                $debitSum = (float) ($totals->total_debit ?? 0);
                $creditSum = (float) ($totals->total_credit ?? 0);

                $storedBalance = (float) $account->balance;

                // Calculate expected balance based on account normal side
                $expectedBalance = $account->isDebitNormal() ? ($debitSum - $creditSum) : ($creditSum - $debitSum);

                // Check for drift (tolerance for floating point)
                if (abs($storedBalance - $expectedBalance) > 0.001) {
                    $discrepanciesCount++;
                    $delta = $expectedBalance - $storedBalance;
                    $status = $fix ? ' [REPAIRED]' : ' [AUDIT REVEALED DRIFT]';

                    $this->line("\n" . ($fix ? '✅' : '🔴') . $status . " Account [{$account->code}] {$account->name}");
                    $this->line("   - Stored Balance:   " . number_format($storedBalance, 2));
                    $this->line("   - Ledger Sum:       " . number_format($expectedBalance, 2));
                    $this->line("   - Drift (Delta):    " . number_format($delta, 2));

                    if ($fix) {
                        $account->balance = $expectedBalance;
                        $account->save();
                    }
                }

                $bar->advance();
            }
        });

        $bar->finish();

        $this->line("\n");
        $this->info("✨ Audit Complete!");

        if ($discrepanciesCount > 0) {
            if (!$fix) {
                $this->warn("Found {$discrepanciesCount} accounts with balance drift.");
                $this->info("Run 'php artisan accounting:reconcile --fix' to align stored balances with the ledger.");
            } else {
                $this->info("Successfully repaired {$discrepanciesCount} account(s).");
            }
        } else {
            $this->info("Perfect Integrity: All account balances match the ledger sum.");
        }

        return 0;
    }
}
