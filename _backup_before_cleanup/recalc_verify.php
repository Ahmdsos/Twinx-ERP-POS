<?php

use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Starting Verified Recalculation...\n";

$accounts = Account::all();
$updatedCount = 0;

foreach ($accounts as $account) {
    // Sum debits and credits from POSTED journal entries only
    $totals = JournalEntryLine::where('account_id', $account->id)
        ->whereHas('journalEntry', function ($query) {
            $query->where('status', 'posted');
        })
        ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
        ->first();

    $debit = $totals->total_debit ?? 0;
    $credit = $totals->total_credit ?? 0;

    // Calculate new balance based on normal side
    $newBalance = 0;
    if ($account->isDebitNormal()) {
        $newBalance = $debit - $credit;
    } else {
        $newBalance = $credit - $debit;
    }

    // Force update regardless of old balance to ensure sync
    $account->balance = $newBalance;
    $account->save();

    // VERIFY
    $refreshed = Account::find($account->id);
    if (abs($refreshed->balance - $newBalance) > 0.001) {
        echo "❌ ERROR: Failed to persist balance for {$account->code}. Expected {$newBalance}, got {$refreshed->balance}\n";
    } else {
        if (abs($newBalance) > 0.001) {
            echo "✅ {$account->code} Updated: {$newBalance}\n";
            $updatedCount++;
        }
    }
}

echo "\n📊 Verified Update Complete. {$updatedCount} active balances.\n";
