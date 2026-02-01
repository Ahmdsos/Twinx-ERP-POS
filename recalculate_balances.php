<?php

use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Enums\JournalStatus;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔄 Starting Account Balance Recalculation...\n\n";

$accounts = Account::all();
$updatedCount = 0;

foreach ($accounts as $account) {
    // Skip if header? No, headers might need aggregation, but typically they are just containers.
    // For now, let's update all. If header balances are derived from children in the UI, that's fine.
    // But usually headers in DB might be 0. Let's focus on transactional accounts first.

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
    $oldBalance = $account->balance;
    $newBalance = 0;

    if ($account->isDebitNormal()) {
        $newBalance = $debit - $credit;
    } else {
        $newBalance = $credit - $debit;
    }

    // Update if different
    if (abs($oldBalance - $newBalance) > 0.001) {
        echo "   - Account [{$account->code}] {$account->name}: {$oldBalance} -> {$newBalance}\n";
        $account->balance = $newBalance;
        $account->save();
        $updatedCount++;
    }
}

echo "\n✅ Recalculation Complete.\n";
echo "📊 Updated {$updatedCount} accounts.\n";
