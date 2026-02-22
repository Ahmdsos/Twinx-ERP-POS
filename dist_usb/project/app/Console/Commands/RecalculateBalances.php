<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Enums\JournalStatus;

class RecalculateBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounting:recalculate-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates all account balances based on active journal entry lines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting account balance recalculation...');

        DB::transaction(function () {
            // 1. Reset all account balances to 0
            Account::query()->update(['balance' => 0]);
            $this->info('All account balances reset to 0.');

            // 2. Get totals from Journal Entry Lines (grouped by account)
            // Only join with active (non-deleted) Journal Entries that are POSTED
            $totals = JournalEntryLine::query()
                ->select('account_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
                ->whereHas('journalEntry', function ($query) {
                    $query->where('status', \Modules\Accounting\Enums\JournalStatus::POSTED);
                })
                ->groupBy('account_id')
                ->get();

            $bar = $this->output->createProgressBar($totals->count());
            $bar->start();

            foreach ($totals as $total) {
                $account = Account::find($total->account_id);
                if ($account) {
                    // Update balance based on account type
                    if ($account->isDebitNormal()) {
                        $account->balance = $total->total_debit - $total->total_credit;
                    } else {
                        $account->balance = $total->total_credit - $total->total_debit;
                    }
                    $account->saveQuietly(); // Avoid triggering events if any
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info('Account balances successfully recalculated.');
        });
    }
}
