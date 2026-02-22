<?php
/**
 * Report Audit Script - Diagnose why P&L revenue is only 240 vs 188K actual sales
 */
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Sales\Models\SalesInvoice;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\JournalStatus;
use Modules\Accounting\Enums\AccountType;

echo "=== REPORT DIAGNOSIS ===\n\n";

// 1. Invoice → Journal mapping
echo "--- 1. SALES INVOICES ---\n";
$invoices = SalesInvoice::all();
echo "Total invoices: " . $invoices->count() . "\n";
echo "With journal_entry_id: " . $invoices->whereNotNull('journal_entry_id')->count() . "\n";
echo "Without journal_entry_id: " . $invoices->where('journal_entry_id', null)->count() . "\n\n";

foreach ($invoices as $inv) {
    echo "  INV#{$inv->id} {$inv->invoice_number} | Total={$inv->total} | Paid={$inv->paid_amount} | Status={$inv->status->value} | JE_ID={$inv->journal_entry_id}\n";
}

// 2. All journal entries
echo "\n--- 2. ALL JOURNAL ENTRIES ---\n";
$entries = JournalEntry::with('lines')->get();
echo "Total entries: " . $entries->count() . "\n\n";

foreach ($entries as $je) {
    $statusVal = $je->status instanceof \BackedEnum ? $je->status->value : $je->status;
    echo "  JE#{$je->id} | Status={$statusVal} | Ref={$je->reference} | Date={$je->entry_date} | Source={$je->source_type}\n";
    echo "    Debits={$je->total_debit} Credits={$je->total_credit}\n";
    foreach ($je->lines as $line) {
        $acct = Account::find($line->account_id);
        $code = $acct ? $acct->code : '?';
        $name = $acct ? $acct->name : '?';
        echo "      {$code} {$name}: DR={$line->debit} CR={$line->credit}\n";
    }
    echo "\n";
}

// 3. Revenue account analysis
echo "--- 3. REVENUE ACCOUNTS (4xxx) ---\n";
$revenueAccounts = Account::where('type', AccountType::REVENUE)->get();
foreach ($revenueAccounts as $acct) {
    $debits = JournalEntryLine::where('account_id', $acct->id)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('debit');
    $credits = JournalEntryLine::where('account_id', $acct->id)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('credit');
    echo "  {$acct->code} {$acct->name}: DR={$debits} CR={$credits} Net(CR-DR)=" . ($credits - $debits) . "\n";
}

// 4. Expense account analysis
echo "\n--- 4. EXPENSE ACCOUNTS (5xxx) ---\n";
$expenseAccounts = Account::where('type', AccountType::EXPENSE)->get();
foreach ($expenseAccounts as $acct) {
    $debits = JournalEntryLine::where('account_id', $acct->id)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('debit');
    $credits = JournalEntryLine::where('account_id', $acct->id)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('credit');
    if ($debits > 0 || $credits > 0) {
        echo "  {$acct->code} {$acct->name}: DR={$debits} CR={$credits} Net(DR-CR)=" . ($debits - $credits) . "\n";
    }
}

// 5. Check for DRAFT entries that should be POSTED
echo "\n--- 5. DRAFT JOURNAL ENTRIES ---\n";
$drafts = JournalEntry::where('status', JournalStatus::DRAFT)->get();
echo "Draft entries: " . $drafts->count() . "\n";
foreach ($drafts as $d) {
    echo "  JE#{$d->id} Ref={$d->reference} Source={$d->source_type} Date={$d->entry_date}\n";
}

// 6. Balance equation check
echo "\n--- 6. BALANCE EQUATION CHECK ---\n";
$types = [AccountType::ASSET, AccountType::LIABILITY, AccountType::EQUITY, AccountType::REVENUE, AccountType::EXPENSE];
foreach ($types as $type) {
    $accounts = Account::where('type', $type)->pluck('id');
    $debits = JournalEntryLine::whereIn('account_id', $accounts)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('debit');
    $credits = JournalEntryLine::whereIn('account_id', $accounts)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('credit');
    $typeVal = $type instanceof \BackedEnum ? $type->value : $type;
    echo "  {$typeVal}: DR={$debits} CR={$credits} Balance=" . ($debits - $credits) . "\n";
}

echo "\n=== END AUDIT ===\n";
