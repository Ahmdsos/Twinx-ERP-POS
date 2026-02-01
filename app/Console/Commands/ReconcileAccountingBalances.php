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
    protected $signature = 'accounting:reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all account balances from posted journal entry lines to ensure data integrity.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Accounting Reconciliation...');

        $accounts = Account::postable()->get();
        $bar = $this->output->createProgressBar($accounts->count());

        $bar->start();

        DB::transaction(function () use ($accounts, $bar) {
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

                $oldBalance = (float) $account->balance;

                // Update balance using Account model logic (respects Normal Balance)
                $account->updateBalance($debitSum, $creditSum);

                if (abs($oldBalance - (float) $account->balance) > 0.001) {
                    $this->line("\n⚠️ Corrected account [{$account->code}] {$account->name}: {$oldBalance} -> {$account->balance}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\n\n✅ Reconciliation complete! All account balances are now in sync with the General Ledger.");

        return 0;
    }
}
