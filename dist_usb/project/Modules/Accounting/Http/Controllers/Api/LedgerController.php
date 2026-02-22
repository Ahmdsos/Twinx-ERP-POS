<?php

namespace Modules\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Services\LedgerService;
use Modules\Accounting\Models\Account;

/**
 * LedgerController - API for Ledger queries and reports
 * 
 * Endpoints:
 * - GET /api/v1/ledger/trial-balance         - Get trial balance
 * - GET /api/v1/ledger/account/{id}          - Get account ledger
 * - GET /api/v1/ledger/balance/{id}          - Get account balance
 * - GET /api/v1/ledger/profit-loss           - Get P&L statement
 */
class LedgerController extends Controller
{
    public function __construct(
        protected LedgerService $ledgerService
    ) {
    }

    /**
     * Get trial balance
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $asOfDate = $request->has('as_of_date')
            ? Carbon::parse($request->input('as_of_date'))
            : null;

        $trialBalance = $this->ledgerService->getTrialBalance($asOfDate);
        $totals = $this->ledgerService->getTrialBalanceTotals($asOfDate);

        return response()->json([
            'success' => true,
            'data' => [
                'accounts' => $trialBalance->values(),
                'totals' => $totals,
            ],
        ]);
    }

    /**
     * Get account ledger (transaction history)
     */
    public function accountLedger(Request $request, Account $account): JsonResponse
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->input('start_date'))
            : null;

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->input('end_date'))
            : null;

        $limit = min($request->input('limit', 100), 500);

        $ledger = $this->ledgerService->getAccountLedger(
            $account->id,
            $startDate,
            $endDate,
            $limit
        );

        $balance = $this->ledgerService->calculateBalance(
            $account->id,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type->label(),
                ],
                'transactions' => $ledger,
                'summary' => $balance,
            ],
        ]);
    }

    /**
     * Get account balance
     */
    public function accountBalance(Request $request, Account $account): JsonResponse
    {
        $startDate = $request->has('start_date')
            ? Carbon::parse($request->input('start_date'))
            : null;

        $endDate = $request->has('end_date')
            ? Carbon::parse($request->input('end_date'))
            : null;

        $balance = $this->ledgerService->calculateBalance(
            $account->id,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => [
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                ],
                'balance' => $balance,
            ],
        ]);
    }

    /**
     * Get Profit and Loss statement
     */
    public function profitAndLoss(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $pnl = $this->ledgerService->getProfitAndLoss($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $pnl,
        ]);
    }

    /**
     * Get balances grouped by account type
     */
    public function balancesByType(Request $request): JsonResponse
    {
        $asOfDate = $request->has('as_of_date')
            ? Carbon::parse($request->input('as_of_date'))
            : null;

        $balances = $this->ledgerService->getBalancesByType($asOfDate);

        return response()->json([
            'success' => true,
            'data' => $balances,
        ]);
    }
}
