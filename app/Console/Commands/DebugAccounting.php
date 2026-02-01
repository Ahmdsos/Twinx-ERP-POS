<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;

class DebugAccounting extends Command
{
    protected $signature = 'debug:accounting {account_id?}';
    protected $description = 'Debugs accounting data integrity';

    public function handle()
    {
        $input = $this->argument('account_id');

        if ($input) {
            // Try to find by ID
            $account = Account::find($input);

            // If not found, try by Code
            if (!$account) {
                $account = Account::where('code', $input)->first();
            }

            if ($account) {
                $this->inspectAccount($account->id);
            } else {
                $this->error("Account not found with ID or Code: $input");
            }
        } else {
            $this->inspectGeneral();
        }
    }

    private function inspectGeneral()
    {
        $this->info("=== General Accounting Inspection ===");

        $totalDebit = DB::table('journal_entry_lines')->sum('debit');
        $totalCredit = DB::table('journal_entry_lines')->sum('credit');

        $this->line("Total System Debit:  " . number_format($totalDebit, 2));
        $this->line("Total System Credit: " . number_format($totalCredit, 2));
        $this->line("Diff: " . number_format($totalDebit - $totalCredit, 2));

        if (abs($totalDebit - $totalCredit) > 0.001) {
            $this->error("CRITICAL: System is out of balance!");
        } else {
            $this->info("System is balanced.");
        }

        $this->info("\n--- Accounts with Balances ---");
        $accounts = Account::all();
        foreach ($accounts as $acc) {
            $debit = DB::table('journal_entry_lines')->where('account_id', $acc->id)->sum('debit');
            $credit = DB::table('journal_entry_lines')->where('account_id', $acc->id)->sum('credit');
            $balance = $debit - $credit;

            if (abs($balance) > 0) {
                $type = $acc->type instanceof AccountType ? $acc->type->value : $acc->type; // Handle Enum or string
                $this->line("[{$acc->code}] {$acc->name} ($type): Dr: " . number_format($debit, 2) . " | Cr: " . number_format($credit, 2) . " | Net: " . number_format($balance, 2));
            }
        }
    }

    private function inspectAccount($id)
    {
        $this->info("=== Inspecting Account ID: $id ===");

        // 1. Get raw lines without join to see if they exist at all
        $rawLines = DB::table('journal_entry_lines')->where('account_id', $id)->get();

        if ($rawLines->isEmpty()) {
            $this->warn("No lines found for this account in 'journal_entry_lines' table.");
            return;
        }

        $this->info("Found {$rawLines->count()} lines in database. Checking relationships...");

        foreach ($rawLines as $line) {
            $parent = DB::table('journal_entries')->where('id', $line->journal_entry_id)->first();

            $dr = number_format($line->debit, 2);
            $cr = number_format($line->credit, 2);

            if (!$parent) {
                $this->error("❌ ORPHAN (Ghost Data): Line #{$line->id} (Dr:{$dr} / Cr:{$cr}) points to missing Entry ID #{$line->journal_entry_id}");
            } else {
                $deleted = $parent->deleted_at ? "[DELETED]" : "";
                $this->line("✅ Line #{$line->id} -> Entry #{$parent->id} [{$parent->status}] {$deleted} | Dr: {$dr} | Cr: {$cr}");
            }
        }
    }
}
