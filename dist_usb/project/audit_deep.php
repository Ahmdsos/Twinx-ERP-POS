<?php
/**
 * TWINX ERP — Deep System Audit v2
 * ==================================
 * Comprehensive validation of all accounting, inventory, sales,
 * payroll, and reporting integrity.
 *
 * Usage: php audit_deep.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Enums\AccountType;
use Modules\Accounting\Enums\JournalStatus;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\StockMovement;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\CustomerPayment;
use Modules\Finance\Models\Expense;

$pass = 0;
$fail = 0;
$warn = 0;
$details = [];

function audit_check(string $label, bool $condition, string $detail, int &$pass, int &$fail, int &$warn, array &$details, string $level = 'error'): void
{
    if ($condition) {
        echo "  ✅ {$label}\n";
        $pass++;
    } elseif ($level === 'warn') {
        echo "  ⚠️  {$label}" . ($detail ? " — {$detail}" : "") . "\n";
        $warn++;
        if ($detail)
            $details[] = "[WARN] {$label}: {$detail}";
    } else {
        echo "  ❌ {$label}" . ($detail ? " — {$detail}" : "") . "\n";
        $fail++;
        if ($detail)
            $details[] = "[FAIL] {$label}: {$detail}";
    }
}

/**
 * Determine if an account type is credit-normal.
 * Revenue, Liability, Equity accounts store positive balances for credits.
 */
function isCreditNormal(AccountType $type): bool
{
    return in_array($type, [AccountType::REVENUE, AccountType::LIABILITY, AccountType::EQUITY]);
}

echo "\n" . str_repeat('═', 65) . "\n";
echo "  🔍 TWINX ERP — Deep System Audit v2\n";
echo "  " . now()->format('Y-m-d H:i:s') . "\n";
echo str_repeat('═', 65) . "\n\n";

// ================================================================
// SECTION 1: Journal Entry Integrity
// ================================================================
echo "━━━ 1. JOURNAL ENTRY INTEGRITY ━━━\n\n";

$allJEs = JournalEntry::all();
$unbalanced = 0;
foreach ($allJEs as $je) {
    $d = round($je->lines()->sum('debit'), 2);
    $c = round($je->lines()->sum('credit'), 2);
    if (abs($d - $c) > 0.01) {
        $unbalanced++;
        $status = is_object($je->status) ? $je->status->value : $je->status;
        echo "  ❌ JE#{$je->id} UNBALANCED: D={$d} C={$c} diff=" . round($d - $c, 2) . " [status={$status}]\n";
        $fail++;
    }
}
audit_check("All {$allJEs->count()} journal entries balanced (DR=CR)", $unbalanced === 0, "{$unbalanced} unbalanced", $pass, $fail, $warn, $details);

// No zero-amount posted JEs
$zeroJEs = JournalEntry::where('status', JournalStatus::POSTED)->get()->filter(function ($je) {
    return $je->lines()->sum('debit') == 0 && $je->lines()->sum('credit') == 0;
});
audit_check("No zero-amount posted journal entries", $zeroJEs->count() === 0, "IDs: " . $zeroJEs->pluck('id')->implode(','), $pass, $fail, $warn, $details);

// Orphan JE lines
$orphanLines = JournalEntryLine::whereNotIn('journal_entry_id', JournalEntry::pluck('id'))->count();
audit_check("No orphan journal entry lines", $orphanLines === 0, "{$orphanLines} orphan lines", $pass, $fail, $warn, $details);

// Lines referencing invalid accounts
$validAccountIds = Account::pluck('id');
$badAccountLines = JournalEntryLine::whereNotIn('account_id', $validAccountIds)->count();
audit_check("All JE lines reference valid accounts", $badAccountLines === 0, "{$badAccountLines} bad references", $pass, $fail, $warn, $details);

// Postings to header accounts
$headerIds = Account::where('is_header', true)->pluck('id');
$headerPostings = JournalEntryLine::whereIn('account_id', $headerIds)->count();
audit_check("No postings to header (non-postable) accounts", $headerPostings === 0, "{$headerPostings} lines posted to headers", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 2: Account Balance Accuracy
// ================================================================
echo "━━━ 2. ACCOUNT BALANCE ACCURACY ━━━\n\n";
echo "  Verifying stored balances match posted JE line calculations...\n";

$balanceMismatches = 0;
$accounts = Account::where('is_header', false)->get();
foreach ($accounts as $account) {
    $debits = JournalEntryLine::where('account_id', $account->id)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('debit');
    $credits = JournalEntryLine::where('account_id', $account->id)
        ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))
        ->sum('credit');

    // For credit-normal accounts (Revenue, Liability, Equity):
    //   Natural balance = credits - debits (positive when in normal state)
    // For debit-normal accounts (Asset, Expense):
    //   Natural balance = debits - credits (positive when in normal state)
    $accountType = is_object($account->type) ? $account->type : AccountType::from($account->type);
    if (isCreditNormal($accountType)) {
        $expected = round($credits - $debits, 2);
    } else {
        $expected = round($debits - $credits, 2);
    }
    $actual = round((float) $account->balance, 2);

    if (abs($expected - $actual) > 0.01) {
        echo "  ❌ {$account->code} {$account->name}: stored={$actual}, calculated={$expected} (diff=" . round($actual - $expected, 2) . ")\n";
        $balanceMismatches++;
        $fail++;
    }
}
audit_check("All {$accounts->count()} leaf account balances match JE calculations", $balanceMismatches === 0, "{$balanceMismatches} mismatches", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 3: Trial Balance & Accounting Equation
// ================================================================
echo "━━━ 3. TRIAL BALANCE & ACCOUNTING EQUATION ━━━\n\n";

$totalDebits = round(JournalEntryLine::whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))->sum('debit'), 2);
$totalCredits = round(JournalEntryLine::whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED))->sum('credit'), 2);

echo "  Total Debits:  " . number_format($totalDebits, 2) . "\n";
echo "  Total Credits: " . number_format($totalCredits, 2) . "\n";
audit_check("Trial Balance: Total Debits = Total Credits", abs($totalDebits - $totalCredits) < 0.01, "D={$totalDebits} C={$totalCredits}", $pass, $fail, $warn, $details);

// Account type summaries
$assetBal = Account::where('type', AccountType::ASSET)->where('is_header', false)->sum('balance');
$liabilityBal = Account::where('type', AccountType::LIABILITY)->where('is_header', false)->sum('balance');
$equityBal = Account::where('type', AccountType::EQUITY)->where('is_header', false)->sum('balance');
$revenueBal = Account::where('type', AccountType::REVENUE)->where('is_header', false)->sum('balance');
$expenseBal = Account::where('type', AccountType::EXPENSE)->where('is_header', false)->sum('balance');

echo "\n  Account Type Balances:\n";
echo "    Assets:      " . number_format($assetBal, 2) . "\n";
echo "    Liabilities: " . number_format($liabilityBal, 2) . "\n";
echo "    Equity:      " . number_format($equityBal, 2) . "\n";
echo "    Revenue:     " . number_format($revenueBal, 2) . "\n";
echo "    Expenses:    " . number_format($expenseBal, 2) . "\n";

// Accounting equation: Assets = Liabilities + Equity + (Revenue - Expenses)
$lhs = round($assetBal, 2);
$rhs = round($liabilityBal + $equityBal + $revenueBal - $expenseBal, 2);
echo "\n    Assets ({$lhs}) = Liabilities + Equity + Net Income ({$rhs})\n";
audit_check("Accounting Equation: Assets = L + E + Net Income", abs($lhs - $rhs) < 0.01, "A={$lhs} vs L+E+NI={$rhs}", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 4: Sales Invoice Integration
// ================================================================
echo "━━━ 4. SALES INVOICE INTEGRATION ━━━\n\n";

$invoices = SalesInvoice::all();
$invoiceIssues = 0;

foreach ($invoices as $inv) {
    $status = is_object($inv->status) ? $inv->status->value : $inv->status;

    // Non-draft invoices must have journal entries
    if (!in_array($status, ['draft', 'cancelled', 'voided'])) {
        if (!$inv->journal_entry_id) {
            echo "  ❌ Invoice {$inv->invoice_number} (status={$status}): NO journal entry\n";
            $invoiceIssues++;
            $fail++;
        } else {
            $je = JournalEntry::find($inv->journal_entry_id);
            if ($je) {
                $jeTotal = $je->lines()->sum('debit');
                if ($jeTotal == 0 && $inv->total > 0) {
                    echo "  ❌ Invoice {$inv->invoice_number}: JE#{$je->id} has zero amounts (total={$inv->total})\n";
                    $invoiceIssues++;
                    $fail++;
                }
            }
        }
    }

    // Balance due consistency
    $paidAmount = (float) ($inv->paid_amount ?? 0);
    $expectedBalance = max(0, round($inv->total - $paidAmount, 2));
    $actualBalance = round((float) ($inv->balance_due ?? 0), 2);
    if (abs($actualBalance - $expectedBalance) > 0.01) {
        echo "  ⚠️  Invoice {$inv->invoice_number}: balance_due={$actualBalance}, expected={$expectedBalance}\n";
        $invoiceIssues++;
        $warn++;
    }
}
audit_check("All {$invoices->count()} invoices properly integrated", $invoiceIssues === 0, "{$invoiceIssues} issues", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 5: Customer Payments Integration
// ================================================================
echo "━━━ 5. CUSTOMER PAYMENTS INTEGRATION ━━━\n\n";

$payments = CustomerPayment::all();
$paymentIssues = 0;

foreach ($payments as $pmt) {
    // POS payments (from POS invoices) embed their JE in the invoice JE
    // They may not have their own journal_entry_id — check if their invoice has one
    $isPOS = false;
    if (!$pmt->journal_entry_id) {
        // Check if this payment is linked to a POS invoice that already has a JE
        $allocations = DB::table('customer_payment_allocations')
            ->where('customer_payment_id', $pmt->id)
            ->get();
        foreach ($allocations as $alloc) {
            $inv = SalesInvoice::find($alloc->sales_invoice_id ?? $alloc->invoice_id ?? null);
            if ($inv && $inv->journal_entry_id) {
                $ref = $inv->invoice_number ?? '';
                if (str_starts_with($ref, 'POS-')) {
                    $isPOS = true;
                    break;
                }
            }
        }

        // Also check by reference pattern
        if (!$isPOS && $pmt->reference && str_starts_with($pmt->reference, 'POS-')) {
            $isPOS = true;
        }

        if (!$isPOS) {
            echo "  ❌ Payment #{$pmt->id} (amount={$pmt->amount}): NO journal entry\n";
            $paymentIssues++;
            $fail++;
        } else {
            echo "  ℹ️  Payment #{$pmt->id}: POS payment (JE embedded in sale entry)\n";
        }
    }

    // Payment account must be Cash/Bank (11xx), not AR
    if (!$isPOS) {
        $paymentAccount = Account::find($pmt->payment_account_id);
        if ($paymentAccount) {
            $code = $paymentAccount->code;
            if (str_starts_with($code, '12')) {
                echo "  ❌ Payment #{$pmt->id}: uses AR account {$code} as payment account (AR wash bug)\n";
                $paymentIssues++;
                $fail++;
            }
        }
    }

    // JE amount consistency
    if ($pmt->journal_entry_id) {
        $je = JournalEntry::find($pmt->journal_entry_id);
        if ($je) {
            $jeDebit = $je->lines()->sum('debit');
            if (abs($jeDebit - $pmt->amount) > 0.01) {
                echo "  ⚠️  Payment #{$pmt->id}: JE debit={$jeDebit} vs amount={$pmt->amount}\n";
                $paymentIssues++;
                $warn++;
            }
        }
    }
}
audit_check("All {$payments->count()} payments valid", $paymentIssues === 0, "{$paymentIssues} issues", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 6: Inventory & COGS Integrity
// ================================================================
echo "━━━ 6. INVENTORY & COGS INTEGRITY ━━━\n\n";

$invIssues = 0;

// Zero-cost inward movements for products with cost_price
$zeroCostInward = StockMovement::where('unit_cost', 0)->where('quantity', '>', 0)->get();
foreach ($zeroCostInward as $sm) {
    $prod = Product::find($sm->product_id);
    if ($prod && $prod->cost_price > 0) {
        echo "  ❌ SM#{$sm->id} ({$prod->name}): inward with unit_cost=0 but product cost_price={$prod->cost_price}\n";
        $invIssues++;
        $fail++;
    }
}

// ProductStock quantity vs net movements
$productStocks = ProductStock::all();
foreach ($productStocks as $ps) {
    $netQty = round(StockMovement::where('product_id', $ps->product_id)->where('warehouse_id', $ps->warehouse_id)->sum('quantity'), 4);
    $stockQty = round($ps->quantity, 4);
    if (abs($netQty - $stockQty) > 0.01) {
        $prod = Product::find($ps->product_id);
        echo "  ❌ ProductStock {$prod->name} WH#{$ps->warehouse_id}: qty={$stockQty}, net movements={$netQty}\n";
        $invIssues++;
        $fail++;
    }
}

// Zero average cost on positive stock
foreach ($productStocks as $ps) {
    if ($ps->quantity > 0 && $ps->average_cost <= 0) {
        $prod = Product::find($ps->product_id);
        if ($prod && $prod->cost_price > 0) {
            echo "  ❌ ProductStock {$prod->name}: qty={$ps->quantity} avg_cost=0 (cost_price={$prod->cost_price})\n";
            $invIssues++;
            $fail++;
        }
    }
}

// Sale movements must have COGS JEs with correct amounts
$saleMvts = StockMovement::whereIn('type', ['sale', 'SALE'])->get();
foreach ($saleMvts as $sm) {
    if (!$sm->journal_entry_id) {
        echo "  ❌ Sale SM#{$sm->id}: no COGS journal entry\n";
        $invIssues++;
        $fail++;
    } else {
        $je = JournalEntry::find($sm->journal_entry_id);
        if ($je && $je->lines()->sum('debit') == 0) {
            echo "  ❌ Sale SM#{$sm->id}: JE#{$je->id} has zero COGS\n";
            $invIssues++;
            $fail++;
        }
    }
}

// COGS cross-check
$cogsAcct = Account::where('code', '5101')->first();
$inventoryAcct = Account::where('code', '1301')->first();
if ($cogsAcct) {
    $totalSoldCost = StockMovement::whereIn('type', ['sale', 'SALE'])->sum(DB::raw('ABS(total_cost)'));
    $totalSoldCost = round($totalSoldCost, 2);
    echo "  COGS (5101) balance: " . number_format($cogsAcct->balance, 2) . " | From movements: " . number_format($totalSoldCost, 2) . "\n";
    audit_check("COGS balance matches total sold cost", abs($cogsAcct->balance - $totalSoldCost) < 0.01, "balance={$cogsAcct->balance} vs movements={$totalSoldCost}", $pass, $fail, $warn, $details);
}
if ($inventoryAcct) {
    // inventory balance should = sum of inward costs - sum of outward costs
    $inwardCost = round(StockMovement::where('quantity', '>', 0)->sum('total_cost'), 2);
    $outwardCost = round(abs(StockMovement::where('quantity', '<', 0)->sum('total_cost')), 2);
    $expectedInvBal = $inwardCost - $outwardCost;
    echo "  Inventory (1301) balance: " . number_format($inventoryAcct->balance, 2) . " | Expected: " . number_format($expectedInvBal, 2) . "\n";
    audit_check("Inventory balance matches stock value", abs($inventoryAcct->balance - $expectedInvBal) < 0.01, "stored={$inventoryAcct->balance} vs calculated={$expectedInvBal}", $pass, $fail, $warn, $details);
}

audit_check("Inventory data integrity — no zero-cost or quantity mismatches", $invIssues === 0, "{$invIssues} issues", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 7: Expense Integration
// ================================================================
echo "━━━ 7. EXPENSE INTEGRATION ━━━\n\n";

$expIssues = 0;
$allExpenses = Expense::all();

foreach ($allExpenses as $exp) {
    $status = $exp->status;

    if ($status === 'approved' && !$exp->journal_entry_id) {
        echo "  ❌ {$exp->reference_number}: approved but no journal entry\n";
        $expIssues++;
        $fail++;
    }

    if ($exp->journal_entry_id) {
        $je = JournalEntry::find($exp->journal_entry_id);
        if ($je) {
            $jeDebit = $je->lines()->sum('debit');
            if (abs($jeDebit - $exp->total_amount) > 0.01) {
                echo "  ⚠️  {$exp->reference_number}: JE debit={$jeDebit} ≠ amount={$exp->total_amount}\n";
                $expIssues++;
                $warn++;
            }
        }
    }

    $cat = \Modules\Finance\Models\ExpenseCategory::find($exp->category_id);
    if ($cat && !$cat->account_id) {
        echo "  ❌ {$exp->reference_number}: category '{$cat->name}' has no account\n";
        $expIssues++;
        $fail++;
    } elseif ($cat && $cat->account_id) {
        $catAcct = Account::find($cat->account_id);
        if ($catAcct && $catAcct->is_header) {
            echo "  ❌ {$exp->reference_number}: category '{$cat->name}' linked to HEADER account\n";
            $expIssues++;
            $fail++;
        }
    }
}
audit_check("All {$allExpenses->count()} expenses valid", $expIssues === 0, "{$expIssues} issues", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 8: Payroll Integration
// ================================================================
echo "━━━ 8. PAYROLL INTEGRATION ━━━\n\n";

$payrollIssues = 0;
try {
    $payrolls = \Modules\HR\Models\Payroll::all();
    foreach ($payrolls as $pr) {
        $status = is_object($pr->status) ? $pr->status->value : $pr->status;
        if (in_array($status, ['approved', 'posted'])) {
            if (!$pr->journal_entry_id) {
                echo "  ❌ Payroll #{$pr->id} (status={$status}): no journal entry\n";
                $payrollIssues++;
                $fail++;
            } else {
                $je = JournalEntry::find($pr->journal_entry_id);
                if ($je && $je->lines()->sum('debit') == 0) {
                    echo "  ❌ Payroll #{$pr->id}: JE#{$je->id} has zero amounts\n";
                    $payrollIssues++;
                    $fail++;
                }
            }
        }
    }
    audit_check("All {$payrolls->count()} payrolls valid", $payrollIssues === 0, "{$payrollIssues} issues", $pass, $fail, $warn, $details);
} catch (\Exception $e) {
    echo "  ⚠️  Payroll check skipped: {$e->getMessage()}\n";
    $warn++;
}

echo "\n";

// ================================================================
// SECTION 9: Settings & Account References
// ================================================================
echo "━━━ 9. SYSTEM SETTINGS VALIDATION ━━━\n\n";

$settingChecks = [
    'acc_ar' => 'Accounts Receivable',
    'acc_cash' => 'Cash Account',
    'acc_sales_revenue' => 'Sales Revenue',
    'acc_cogs' => 'Cost of Goods Sold',
    'acc_inventory' => 'Inventory Asset',
    'acc_inventory_adj' => 'Inventory Adjustments',
    'acc_tax_payable' => 'Tax Payable',
    'acc_salaries_exp' => 'Salaries Expense',
    'acc_opening_balance' => 'Opening Balance Equity',
];

foreach ($settingChecks as $key => $label) {
    $code = \App\Models\Setting::getValue($key);
    if (!$code) {
        audit_check("{$key} → {$label}", false, "NOT SET", $pass, $fail, $warn, $details, 'warn');
        continue;
    }
    $acct = Account::where('code', $code)->first();
    if (!$acct) {
        audit_check("{$key} → {$code}", false, "account NOT FOUND", $pass, $fail, $warn, $details);
    } elseif ($acct->is_header) {
        audit_check("{$key} → {$code} ({$acct->name})", false, "HEADER account — can't post", $pass, $fail, $warn, $details);
    } elseif (isset($acct->is_active) && !$acct->is_active) {
        audit_check("{$key} → {$code} ({$acct->name})", false, "INACTIVE", $pass, $fail, $warn, $details, 'warn');
    } else {
        echo "  ✅ {$key} → {$code} ({$acct->name})\n";
        $pass++;
    }
}

echo "\n";

// ================================================================
// SECTION 10: Expense Category Account Linkage
// ================================================================
echo "━━━ 10. EXPENSE CATEGORIES ━━━\n\n";

$cats = \Modules\Finance\Models\ExpenseCategory::all();
foreach ($cats as $cat) {
    if (!$cat->account_id) {
        audit_check("Category '{$cat->name}'", false, "NO account linked", $pass, $fail, $warn, $details);
    } else {
        $acct = Account::find($cat->account_id);
        if (!$acct) {
            audit_check("Category '{$cat->name}'", false, "account_id={$cat->account_id} NOT FOUND", $pass, $fail, $warn, $details);
        } elseif ($acct->is_header) {
            audit_check("Category '{$cat->name}'", false, "linked to HEADER {$acct->code}", $pass, $fail, $warn, $details);
        } else {
            echo "  ✅ {$cat->name} → {$acct->code} ({$acct->name})\n";
            $pass++;
        }
    }
}

echo "\n";

// ================================================================
// SECTION 11: P&L Report Verification
// ================================================================
echo "━━━ 11. P&L REPORT VERIFICATION ━━━\n\n";

$periodStart = now()->startOfYear();
$periodEnd = now();
echo "  Period: {$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')}\n\n";

// Revenue from JE lines
$revenueAccountIds = Account::where('type', AccountType::REVENUE)->where('is_header', false)->pluck('id');
$revCredits = JournalEntryLine::whereIn('account_id', $revenueAccountIds)
    ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))
    ->sum('credit');
$revDebits = JournalEntryLine::whereIn('account_id', $revenueAccountIds)
    ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))
    ->sum('debit');
$netRevenue = round($revCredits - $revDebits, 2);

// Expense from JE lines
$expenseAccountIds = Account::where('type', AccountType::EXPENSE)->where('is_header', false)->pluck('id');
$expDebits = JournalEntryLine::whereIn('account_id', $expenseAccountIds)
    ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))
    ->sum('debit');
$expCredits = JournalEntryLine::whereIn('account_id', $expenseAccountIds)
    ->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))
    ->sum('credit');
$netExpenses = round($expDebits - $expCredits, 2);

$netPL = round($netRevenue - $netExpenses, 2);

echo "  ┌──────────────────────────────────────────┐\n";
echo "  │  Revenue Breakdown                       │\n";
echo "  ├──────────────────────────────────────────┤\n";
$revAccounts = Account::where('type', AccountType::REVENUE)->where('is_header', false)->get();
foreach ($revAccounts as $a) {
    $cr = JournalEntryLine::where('account_id', $a->id)->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))->sum('credit');
    $dr = JournalEntryLine::where('account_id', $a->id)->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))->sum('debit');
    $bal = round($cr - $dr, 2);
    if (abs($bal) > 0.01) {
        echo "  │  {$a->code} {$a->name}: " . number_format($bal, 2) . "\n";
    }
}
echo "  │  ───────────────────────────────────────\n";
echo "  │  Total Revenue: " . number_format($netRevenue, 2) . "\n";
echo "  ├──────────────────────────────────────────┤\n";
echo "  │  Expense Breakdown                       │\n";
echo "  ├──────────────────────────────────────────┤\n";
$expAccounts = Account::where('type', AccountType::EXPENSE)->where('is_header', false)->get();
foreach ($expAccounts as $a) {
    $dr = JournalEntryLine::where('account_id', $a->id)->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))->sum('debit');
    $cr = JournalEntryLine::where('account_id', $a->id)->whereHas('journalEntry', fn($q) => $q->where('status', JournalStatus::POSTED)->whereDate('entry_date', '>=', $periodStart)->whereDate('entry_date', '<=', $periodEnd))->sum('credit');
    $bal = round($dr - $cr, 2);
    if (abs($bal) > 0.01) {
        $sign = $bal < 0 ? ' (CONTRA)' : '';
        echo "  │  {$a->code} {$a->name}: " . number_format($bal, 2) . "{$sign}\n";
    }
}
echo "  │  ───────────────────────────────────────\n";
echo "  │  Total Expenses: " . number_format($netExpenses, 2) . "\n";
echo "  ├──────────────────────────────────────────┤\n";
echo "  │  NET PROFIT/LOSS: " . number_format($netPL, 2) . "\n";
echo "  └──────────────────────────────────────────┘\n";

// Cross-check with stored balances
echo "\n  Cross-check vs stored account balances:\n";
echo "    Revenue (stored): " . number_format($revenueBal, 2) . " | From JEs: " . number_format($netRevenue, 2) . "\n";
echo "    Expenses (stored): " . number_format($expenseBal, 2) . " | From JEs: " . number_format($netExpenses, 2) . "\n";

audit_check("Revenue stored balance matches JE calculation", abs(abs($revenueBal) - abs($netRevenue)) < 0.01, "stored=" . abs($revenueBal) . " vs JE={$netRevenue}", $pass, $fail, $warn, $details);
audit_check("Expense stored balance matches JE calculation", abs($expenseBal - $netExpenses) < 0.01, "stored={$expenseBal} vs JE={$netExpenses}", $pass, $fail, $warn, $details);

echo "\n";

// ================================================================
// SECTION 12: Data Consistency Checks
// ================================================================
echo "━━━ 12. DATA CONSISTENCY ━━━\n\n";

// Products without cost price that have stock movements
$noCostProducts = Product::where(function ($q) {
    $q->whereNull('cost_price')->orWhere('cost_price', 0);
})->get();
foreach ($noCostProducts as $p) {
    $hasMvts = StockMovement::where('product_id', $p->id)->exists();
    if ($hasMvts) {
        audit_check("Product '{$p->name}' has cost_price", false, "cost_price=0 but has stock movements", $pass, $fail, $warn, $details, 'warn');
    }
}

// Duplicate references
$dupRefs = JournalEntry::select('reference', DB::raw('COUNT(*) as cnt'))
    ->whereNotNull('reference')
    ->where('reference', '!=', '')
    ->groupBy('reference')
    ->having('cnt', '>', 1)
    ->get();
foreach ($dupRefs as $d) {
    audit_check("Unique JE reference '{$d->reference}'", false, "{$d->cnt} duplicates", $pass, $fail, $warn, $details, 'warn');
}

echo "\n";

// ================================================================
// SUMMARY
// ================================================================
echo str_repeat('═', 65) . "\n";
echo "  📊 AUDIT SUMMARY\n";
echo str_repeat('═', 65) . "\n";
echo "  ✅ Passed:   {$pass}\n";
echo "  ❌ Failed:   {$fail}\n";
echo "  ⚠️  Warnings: {$warn}\n";
echo "  Total:      " . ($pass + $fail + $warn) . "\n";

if ($fail === 0 && $warn === 0) {
    echo "\n  🎉 ALL CHECKS PASSED — System integrity is fully verified!\n";
} elseif ($fail === 0) {
    echo "\n  ✅ No critical failures. {$warn} warnings to review.\n";
} else {
    echo "\n  🚨 {$fail} CRITICAL ISSUES — Run php repair_journals.php\n";
}

if (!empty($details)) {
    echo "\n  Issue Details:\n";
    foreach (array_slice($details, 0, 25) as $d) {
        echo "    • {$d}\n";
    }
    if (count($details) > 25) {
        echo "    ... and " . (count($details) - 25) . " more\n";
    }
}

echo str_repeat('═', 65) . "\n\n";
