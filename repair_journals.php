<?php
/**
 * Data Repair Script for Twinx ERP
 * ====================================
 * 
 * Fixes incorrect journal entries identified in the DB audit:
 * 
 * 1. Zero-amount sales invoice journals (Bug 1)
 *    - Finds invoices with JEs that have zero debit/credit totals
 *    - Reverses the bad JE, then creates a correct one using current invoice totals
 * 
 * 2. AR-wash payment journals (Bug 2)
 *    - Finds customer payments where payment_account_id points to an AR account
 *    - These create DR AR / CR AR (self-canceling) entries
 *    - Fix: Reverse bad JE, update payment_account_id to Cash, recreate JE
 * 
 * Usage: php artisan tinker --execute="require 'repair_journals.php';"
 * Or:    php repair_journals.php (if running from project root with Laravel bootstrap)
 * 
 * IMPORTANT: Run this AFTER deploying the code fixes to prevent new bad entries.
 */

// Bootstrap Laravel if running standalone
if (!defined('LARAVEL_START')) {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\JournalService;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\CustomerPayment;
use Modules\Sales\Services\SalesService;

echo "\n" . str_repeat('=', 60) . "\n";
echo "  TWINX ERP — Data Repair Script\n";
echo str_repeat('=', 60) . "\n\n";

$journalService = app(JournalService::class);
$salesService = app(SalesService::class);
$fixed = 0;
$errors = [];

// ============================================================
// FIX 1: Zero-amount Sales Invoice Journal Entries
// ============================================================
echo "--- FIX 1: Zero-Amount Sales Invoice Journals ---\n\n";

$zeroInvoiceJEs = JournalEntry::where('source_type', SalesInvoice::class)
    ->whereHas('lines', function ($q) {
        // Group and check if ALL debits AND credits are zero
    })
    ->get()
    ->filter(function ($je) {
        $totalDebit = $je->lines()->sum('debit');
        $totalCredit = $je->lines()->sum('credit');
        return $totalDebit == 0 && $totalCredit == 0;
    });

echo "Found " . $zeroInvoiceJEs->count() . " zero-amount invoice JEs\n";

foreach ($zeroInvoiceJEs as $je) {
    $invoice = SalesInvoice::find($je->source_id);
    if (!$invoice) {
        echo "  ⚠ JE#{$je->id}: Invoice #{$je->source_id} not found — skipping\n";
        continue;
    }

    // Reload invoice totals from DB
    $invoice->refresh();

    if ($invoice->total <= 0) {
        echo "  ⚠ JE#{$je->id}: Invoice {$invoice->invoice_number} has total=0 even after refresh — may need manual fix\n";
        continue;
    }

    echo "  🔧 JE#{$je->id}: Invoice {$invoice->invoice_number} — total={$invoice->total}\n";

    try {
        DB::transaction(function () use ($je, $invoice, $journalService, &$fixed) {
            // 1. Delete or reverse the zero JE
            if ($je->status === 'posted' || $je->status->value === 'posted') {
                // Unpost first: reverse account balances (though zero, be safe)
                $journalService->deletePosted($je);
                echo "    ✓ Deleted posted zero JE#{$je->id}\n";
            } else {
                $journalService->delete($je);
                echo "    ✓ Deleted draft zero JE#{$je->id}\n";
            }

            // 2. Clear the old JE link from invoice
            $invoice->update(['journal_entry_id' => null]);

            // 3. Recreate the journal entry using the SalesService method
            // We need to call the protected method via reflection or recreate the logic
            $arCode = \App\Models\Setting::getValue('acc_ar', '1201');
            $salesCode = \App\Models\Setting::getValue('acc_sales_revenue', '4101');
            $taxCode = \App\Models\Setting::getValue('acc_tax_payable', '2201');

            $arAccount = Account::where('code', $arCode)->first();
            $salesAccount = Account::where('code', $salesCode)->first();
            $taxAccount = Account::where('code', $taxCode)->first();

            if (!$arAccount || !$salesAccount) {
                throw new \RuntimeException("Missing AR or Sales Revenue account");
            }

            $subtotal = $invoice->subtotal;
            $taxAmount = $invoice->tax_amount;
            $totalAmount = $invoice->total;

            $lines = [
                // DR: Accounts Receivable
                ['account_id' => $arAccount->id, 'debit' => $totalAmount, 'credit' => 0, 'description' => "فاتورة بيع: {$invoice->invoice_number}"],
                // CR: Sales Revenue
                ['account_id' => $salesAccount->id, 'debit' => 0, 'credit' => $subtotal, 'description' => "إيراد مبيعات: {$invoice->invoice_number}"],
            ];

            // Add tax line if applicable
            if ($taxAmount > 0 && $taxAccount) {
                $lines[] = ['account_id' => $taxAccount->id, 'debit' => 0, 'credit' => $taxAmount, 'description' => "ضريبة مبيعات: {$invoice->invoice_number}"];
            }

            $newEntry = $journalService->create([
                'entry_date' => $invoice->invoice_date,
                'reference' => $invoice->invoice_number,
                'description' => "فاتورة بيع: {$invoice->invoice_number} (إصلاح)",
                'source_type' => SalesInvoice::class,
                'source_id' => $invoice->id,
            ], $lines);

            $journalService->post($newEntry);
            $invoice->update(['journal_entry_id' => $newEntry->id]);

            echo "    ✓ Created new JE#{$newEntry->id} — DR AR {$totalAmount}, CR Revenue {$subtotal}\n";
            $fixed++;
        });
    } catch (\Exception $e) {
        echo "    ✗ ERROR: " . $e->getMessage() . "\n";
        $errors[] = "JE#{$je->id}: " . $e->getMessage();
    }
}

echo "\n";

// ============================================================
// FIX 2: Customer Payment AR-Wash Journal Entries
// ============================================================
echo "--- FIX 2: Customer Payment AR-Wash Journal Entries ---\n\n";

// Find payments where payment_account_id points to an AR account (code 12xx)
$arAccounts = Account::where('code', 'like', '12%')->pluck('id')->toArray();
$badPayments = CustomerPayment::whereIn('payment_account_id', $arAccounts)->get();

echo "Found " . $badPayments->count() . " payments using AR/non-cash accounts\n";

// Find the default cash account for repair
$cashAccount = Account::where('code', '1101')->first();
if (!$cashAccount) {
    $cashAccount = Account::where('code', 'like', '11%')->where('is_active', true)->first();
}

if (!$cashAccount) {
    echo "  ⚠ No Cash/Bank account (11xx) found — cannot fix payment JEs\n";
} else {
    echo "  Using cash account: {$cashAccount->name} ({$cashAccount->code})\n\n";

    foreach ($badPayments as $payment) {
        $customer = $payment->customer;
        echo "  🔧 Payment #{$payment->receipt_number} — amount={$payment->amount}, old account_id={$payment->payment_account_id}\n";

        try {
            DB::transaction(function () use ($payment, $customer, $cashAccount, $journalService, &$fixed) {
                // 1. Reverse old JE if exists
                if ($payment->journal_entry_id) {
                    $oldEntry = JournalEntry::find($payment->journal_entry_id);
                    if ($oldEntry) {
                        $journalService->deletePosted($oldEntry);
                        echo "    ✓ Deleted bad JE#{$oldEntry->id} (DR AR / CR AR)\n";
                    }
                }

                // 2. Update payment to use cash account
                $payment->update([
                    'payment_account_id' => $cashAccount->id,
                    'journal_entry_id' => null,
                ]);

                // 3. Get AR account for the customer
                $arCode = \App\Models\Setting::getValue('acc_ar', '1201');
                $arAccount = $customer && $customer->account_id
                    ? Account::find($customer->account_id)
                    : Account::where('code', $arCode)->first();

                if (!$arAccount) {
                    throw new \RuntimeException("Missing AR account");
                }

                // 4. Create correct JE: DR Cash, CR AR
                $newEntry = $journalService->create([
                    'entry_date' => $payment->payment_date,
                    'reference' => $payment->receipt_number,
                    'description' => "دفعة من عميل: " . ($customer->name ?? 'N/A') . " (إصلاح)",
                    'source_type' => CustomerPayment::class,
                    'source_id' => $payment->id,
                ], [
                    ['account_id' => $cashAccount->id, 'debit' => $payment->amount, 'credit' => 0, 'description' => 'تحصيل نقدي'],
                    [
                        'account_id' => $arAccount->id,
                        'debit' => 0,
                        'credit' => $payment->amount,
                        'description' => 'تسوية ذمم عملاء',
                        'subledger_type' => \Modules\Sales\Models\Customer::class,
                        'subledger_id' => $customer->id ?? null,
                    ],
                ]);

                $journalService->post($newEntry);
                $payment->update(['journal_entry_id' => $newEntry->id]);

                echo "    ✓ Created new JE#{$newEntry->id} — DR Cash {$payment->amount}, CR AR {$payment->amount}\n";
                $fixed++;
            });
        } catch (\Exception $e) {
            echo "    ✗ ERROR: " . $e->getMessage() . "\n";
            $errors[] = "Payment #{$payment->receipt_number}: " . $e->getMessage();
        }
    }
}

echo "\n";

// ============================================================
// FIX 3: Zero-amount Payroll Journal Entries
// ============================================================
echo "--- FIX 3: Zero-Amount Payroll Journal Entries ---\n\n";

$zeroPayrollJEs = JournalEntry::where('source_type', \Modules\HR\Models\Payroll::class)
    ->get()
    ->filter(function ($je) {
        $totalDebit = $je->lines()->sum('debit');
        return $totalDebit == 0;
    });

echo "Found " . $zeroPayrollJEs->count() . " zero-amount payroll JEs\n";

foreach ($zeroPayrollJEs as $je) {
    $payroll = \Modules\HR\Models\Payroll::find($je->source_id);
    if (!$payroll) {
        echo "  ⚠ JE#{$je->id}: Payroll #{$je->source_id} not found — skipping\n";
        continue;
    }

    // Check if payroll actually has zero salary (intentional test data)
    if ($payroll->net_salary <= 0 && $payroll->total_basic <= 0) {
        echo "  ℹ JE#{$je->id}: Payroll has zero salary data — likely test data. Deleting empty JE.\n";
        try {
            DB::transaction(function () use ($je, $payroll, $journalService, &$fixed) {
                if ($je->status === 'posted' || (is_object($je->status) && $je->status->value === 'posted')) {
                    $journalService->deletePosted($je);
                } else {
                    $journalService->delete($je);
                }
                $payroll->update(['journal_entry_id' => null, 'status' => 'draft']);
                echo "    ✓ Deleted zero JE#{$je->id}, payroll reset to draft\n";
                $fixed++;
            });
        } catch (\Exception $e) {
            echo "    ✗ ERROR: " . $e->getMessage() . "\n";
            $errors[] = "Payroll JE#{$je->id}: " . $e->getMessage();
        }
    } else {
        echo "  ⚠ JE#{$je->id}: Payroll has data (net={$payroll->net_salary}) but JE is zero — manual review needed\n";
    }
}

// ============================================================
// FIX 5: Zero-Cost Stock Movements (Causes Missing COGS)
// ============================================================
echo "\n--- FIX 5: Zero-Cost Stock Movements ---\n\n";

$zeroCostMovements = \Modules\Inventory\Models\StockMovement::where('unit_cost', 0)->get();
echo "Found " . $zeroCostMovements->count() . " zero-cost stock movements\n";

foreach ($zeroCostMovements as $movement) {
    $product = \Modules\Inventory\Models\Product::find($movement->product_id);
    if (!$product || $product->cost_price <= 0) {
        echo "  ⚠ SM#{$movement->id}: Product has no cost_price — skipping\n";
        continue;
    }

    $type = is_object($movement->type) ? $movement->type->value : $movement->type;
    $qty = abs($movement->quantity);
    $correctUnitCost = $product->cost_price;
    $correctTotalCost = $qty * $correctUnitCost;
    $sign = $movement->quantity < 0 ? -1 : 1;

    echo "  🔧 SM#{$movement->id} ({$type}) — qty={$movement->quantity}, fixing cost: 0 → {$correctUnitCost}/unit ({$correctTotalCost} total)\n";

    try {
        DB::transaction(function () use ($movement, $correctUnitCost, $correctTotalCost, $sign, $product, $journalService, &$fixed) {
            // 1. Fix the movement record
            $movement->update([
                'unit_cost' => $correctUnitCost,
                'total_cost' => $correctTotalCost * $sign,
            ]);

            // 2. Fix the associated journal entry if it exists
            if ($movement->journal_entry_id) {
                $oldJE = JournalEntry::find($movement->journal_entry_id);
                if ($oldJE) {
                    $totalD = $oldJE->lines()->sum('debit');
                    if ($totalD == 0) {
                        // Delete the zero JE and recreate
                        $statusVal = is_object($oldJE->status) ? $oldJE->status->value : $oldJE->status;
                        if ($statusVal === 'posted') {
                            $journalService->deletePosted($oldJE);
                        } else {
                            $journalService->delete($oldJE);
                        }
                        $movement->update(['journal_entry_id' => null]);
                        echo "    ✓ Deleted zero JE#{$oldJE->id}\n";

                        // Recreate with correct amounts
                        $inventoryService = app(\Modules\Inventory\Services\InventoryService::class);
                        $direction = $movement->quantity > 0 ? 'add' : 'remove';

                        // Use reflection to call the protected method
                        $ref = new \ReflectionMethod($inventoryService, 'createInventoryJournalEntry');
                        $ref->setAccessible(true);
                        $movement->refresh();
                        $ref->invoke($inventoryService, $movement, $product, $direction);

                        $movement->refresh();
                        echo "    ✓ Created new JE#{$movement->journal_entry_id} with correct amounts\n";
                    }
                }
            }

            // 3. Fix ProductStock average_cost and total_value
            $stocks = \Modules\Inventory\Models\ProductStock::where('product_id', $product->id)->get();
            foreach ($stocks as $stock) {
                if ($stock->average_cost == 0 && $stock->quantity > 0) {
                    $stock->update([
                        'average_cost' => $correctUnitCost,
                        'total_value' => $stock->quantity * $correctUnitCost,
                    ]);
                    echo "    ✓ Fixed ProductStock avg_cost={$correctUnitCost}, total_value=" . ($stock->quantity * $correctUnitCost) . "\n";
                }
            }

            $fixed++;
        });
    } catch (\Exception $e) {
        echo "    ✗ ERROR: " . $e->getMessage() . "\n";
        $errors[] = "SM#{$movement->id}: " . $e->getMessage();
    }
}

// ============================================================
// FIX 4: Approved Expenses Without Journal Entries
// ============================================================
echo "\n--- FIX 4: Approved Expenses Without Journal Entries ---\n\n";

$expenseService = app(\Modules\Finance\Services\ExpenseService::class);

// First, fix categories without account_id
$generalExpAccount = Account::where('code', '5223')->first()
    ?? Account::where('code', 'like', '522%')->where('is_header', false)->first()
    ?? Account::where('code', 'like', '52%')->where('is_header', false)->first()
    ?? Account::where('code', 'like', '5%')->where('is_header', false)->first();

if ($generalExpAccount) {
    // Fix categories with NULL account_id
    $unlinkedCats = \Modules\Finance\Models\ExpenseCategory::whereNull('account_id')->get();
    // Also fix categories linked to header/inactive accounts
    $headerAccountIds = Account::where('is_header', true)->orWhere('is_active', false)->pluck('id')->toArray();
    $badLinkedCats = \Modules\Finance\Models\ExpenseCategory::whereIn('account_id', $headerAccountIds)->get();
    $allBadCats = $unlinkedCats->merge($badLinkedCats)->unique('id');

    foreach ($allBadCats as $cat) {
        $cat->update(['account_id' => $generalExpAccount->id]);
        echo "  ✓ Linked category '{$cat->name}' to leaf account {$generalExpAccount->code} ({$generalExpAccount->name})\n";
    }
}

// Find approved expenses without journal entries
$orphanExpenses = \Modules\Finance\Models\Expense::where('status', 'approved')
    ->whereNull('journal_entry_id')
    ->get();

echo "Found " . $orphanExpenses->count() . " approved expenses without journal entries\n";

foreach ($orphanExpenses as $expense) {
    echo "  🔧 {$expense->reference_number} — amount={$expense->total_amount}\n";
    try {
        $expense->refresh(); // reload with updated category
        $expenseService->createJournalEntry($expense);
        $expense->refresh();
        if ($expense->journal_entry_id) {
            echo "    ✓ Created JE#{$expense->journal_entry_id}\n";
            $fixed++;
        } else {
            echo "    ⚠ JE not created — check category account linkage\n";
        }
    } catch (\Exception $e) {
        echo "    ✗ ERROR: " . $e->getMessage() . "\n";
        $errors[] = "Expense {$expense->reference_number}: " . $e->getMessage();
    }
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n" . str_repeat('=', 60) . "\n";
echo "  REPAIR SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "  Fixed entries: {$fixed}\n";
echo "  Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n  Error Details:\n";
    foreach ($errors as $err) {
        echo "    - {$err}\n";
    }
}

echo "\n  ✅ Run `php audit_reports.php` to verify corrections.\n";
echo str_repeat('=', 60) . "\n\n";

