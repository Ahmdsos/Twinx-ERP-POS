<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;

echo "Auditing Accounts...\n";

$acc1301 = Account::where('code', '1301')->first();
$acc5101 = Account::where('code', '5101')->first();
$acc2101 = Account::where('code', '2101')->first();

echo "1301 (Inventory): " . ($acc1301 ? "Found - Type: {$acc1301->type->name}" : "NOT FOUND") . "\n";
echo "5101 (COGS):      " . ($acc5101 ? "Found - Type: {$acc5101->type->name}" : "NOT FOUND") . "\n";
echo "2101 (AP):        " . ($acc2101 ? "Found - Type: {$acc2101->type->name}" : "NOT FOUND") . "\n";

echo "\n--- Balances (Should be Non-Zero) ---\n";

// Check 1301 (Asset - Debit Normal)
if ($acc1301) {
    $debit = DB::table('journal_entry_lines')->where('account_id', $acc1301->id)->sum('debit');
    $credit = DB::table('journal_entry_lines')->where('account_id', $acc1301->id)->sum('credit');
    echo "1301 Inventory Balance (Dr-Cr): " . number_format($debit - $credit, 2) . "\n";
}

// Check 2101 (Liability - Credit Normal)
if ($acc2101) {
    $debit = DB::table('journal_entry_lines')->where('account_id', $acc2101->id)->sum('debit');
    $credit = DB::table('journal_entry_lines')->where('account_id', $acc2101->id)->sum('credit');
    echo "2101 AP Balance (Cr-Dr):        " . number_format($credit - $debit, 2) . "\n";
}

// Check 5101 (Expense - Debit Normal)
if ($acc5101) {
    $debit = DB::table('journal_entry_lines')->where('account_id', $acc5101->id)->sum('debit');
    $credit = DB::table('journal_entry_lines')->where('account_id', $acc5101->id)->sum('credit');
    echo "5101 COGS Balance (Dr-Cr):      " . number_format($debit - $credit, 2) . "\n";
}
