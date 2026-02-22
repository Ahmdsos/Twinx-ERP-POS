<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Finance\Models\TreasuryTransaction;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\JournalService;
use Illuminate\Support\Facades\DB;

class TreasuryTransactionController extends Controller
{
    protected $journalService;

    public function __construct(JournalService $journalService)
    {
        $this->journalService = $journalService;
    }

    /**
     * Display a listing of treasury transactions.
     */
    public function index(Request $request)
    {
        $query = TreasuryTransaction::with(['treasuryAccount', 'counterAccount', 'creator'])
            ->latest('transaction_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('finance.treasury.index', compact('transactions'));
    }

    /**
     * Show form for creating a Payment Voucher (Sanad Sarf).
     */
    public function createPayment()
    {
        // Treasury Accounts: Assets (Cash/Bank) that are not headers
        $treasuryAccounts = Account::where('is_active', true)
            ->where('is_header', false)
            ->where(function ($q) {
                // Get all assets starting with 1 OR accounts named Cash/Bank
                $q->where('code', 'like', '1%')
                    ->orWhere('name', 'like', '%Cash%')
                    ->orWhere('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%Khazna%')
                    ->orWhere('name', 'like', '%خزينة%')
                    ->orWhere('name', 'like', '%بنك%')
                    ->orWhere('name', 'like', '%نقدية%');
            })
            ->get();

        // Counter Accounts: ANY active non-header account
        $counterAccounts = Account::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('finance.treasury.create-payment', compact('treasuryAccounts', 'counterAccounts'));
    }

    /**
     * Store a Payment Voucher.
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'treasury_account_id' => 'required|exists:accounts,id',
            'counter_account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Create Transaction Record
            $transaction = TreasuryTransaction::create([
                'transaction_date' => $validated['transaction_date'],
                'type' => 'payment',
                'amount' => $validated['amount'],
                'treasury_account_id' => $validated['treasury_account_id'],
                'counter_account_id' => $validated['counter_account_id'],
                'description' => $validated['description'],
                'reference' => $validated['reference'],
                'created_by' => auth()->id(),
            ]);

            // 2. Create Journal Entry
            // Payment: Credit Cash (Treasury), Debit Expense/Party (Counter)
            $lines = [
                [
                    'account_id' => $validated['counter_account_id'],
                    'debit' => $validated['amount'],
                    'credit' => 0,
                    'description' => $validated['description'] ?? 'Payment Voucher',
                ],
                [
                    'account_id' => $validated['treasury_account_id'],
                    'debit' => 0,
                    'credit' => $validated['amount'],
                    'description' => 'Cash Out: ' . ($validated['reference'] ?? ''),
                ],
            ];

            $entry = $this->journalService->create([
                'entry_date' => $validated['transaction_date'],
                'reference' => $validated['reference'],
                'description' => "Payment Voucher #{$transaction->id} - " . ($validated['description'] ?? ''),
                'source_type' => TreasuryTransaction::class,
                'source_id' => $transaction->id,
            ], $lines);

            $this->journalService->post($entry);
            $transaction->update(['journal_entry_id' => $entry->id]);
        });

        return redirect()->route('treasury.index')->with('success', 'تم حفظ سند الصرف بنجاح');
    }

    /**
     * Show form for creating a Receipt Voucher (Sanad Qabd).
     */
    public function createReceipt()
    {
        // Treasury Accounts: Assets (Cash/Bank)
        $treasuryAccounts = Account::where('is_active', true)
            ->where('is_header', false)
            ->where(function ($q) {
                $q->where('code', 'like', '1%')
                    ->orWhere('name', 'like', '%Cash%')
                    ->orWhere('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%خزينة%')
                    ->orWhere('name', 'like', '%بنك%')
                    ->orWhere('name', 'like', '%نقدية%');
            })
            ->get();

        // Counter Accounts: ANY active non-header account (Income, Customer, etc)
        $counterAccounts = Account::where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('finance.treasury.create-receipt', compact('treasuryAccounts', 'counterAccounts'));
    }

    /**
     * Store a Receipt Voucher.
     */
    public function storeReceipt(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'treasury_account_id' => 'required|exists:accounts,id',
            'counter_account_id' => 'required|exists:accounts,id',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            // 1. Create Transaction Record
            $transaction = TreasuryTransaction::create([
                'transaction_date' => $validated['transaction_date'],
                'type' => 'receipt',
                'amount' => $validated['amount'],
                'treasury_account_id' => $validated['treasury_account_id'],
                'counter_account_id' => $validated['counter_account_id'],
                'description' => $validated['description'],
                'reference' => $validated['reference'],
                'created_by' => auth()->id(),
            ]);

            // 2. Create Journal Entry
            // Receipt: Debit Cash (Treasury), Credit Income/Party (Counter)
            $lines = [
                [
                    'account_id' => $validated['treasury_account_id'],
                    'debit' => $validated['amount'],
                    'credit' => 0,
                    'description' => 'Cash In: ' . ($validated['reference'] ?? ''),
                ],
                [
                    'account_id' => $validated['counter_account_id'],
                    'debit' => 0,
                    'credit' => $validated['amount'],
                    'description' => $validated['description'] ?? 'Receipt Voucher',
                ]
            ];

            $entry = $this->journalService->create([
                'entry_date' => $validated['transaction_date'],
                'reference' => $validated['reference'],
                'description' => "Receipt Voucher #{$transaction->id} - " . ($validated['description'] ?? ''),
                'source_type' => TreasuryTransaction::class,
                'source_id' => $transaction->id,
            ], $lines);

            $this->journalService->post($entry);
            $transaction->update(['journal_entry_id' => $entry->id]);
        });

        return redirect()->route('treasury.index')->with('success', 'تم حفظ سند القبض بنجاح');
    }

    public function show(TreasuryTransaction $transaction)
    {
        $transaction->load(['treasuryAccount', 'counterAccount', 'creator', 'journalEntry']);
        return view('finance.treasury.show', compact('transaction'));
    }
}
