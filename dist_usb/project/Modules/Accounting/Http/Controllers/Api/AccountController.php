<?php

namespace Modules\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\StoreAccountRequest;
use Modules\Accounting\Http\Requests\UpdateAccountRequest;
use Modules\Accounting\Http\Resources\AccountResource;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountType;

/**
 * AccountController - API for Chart of Accounts management
 * 
 * Endpoints:
 * - GET    /api/v1/accounts           - List accounts (tree or flat)
 * - POST   /api/v1/accounts           - Create account
 * - GET    /api/v1/accounts/{id}      - Show account
 * - PUT    /api/v1/accounts/{id}      - Update account
 * - DELETE /api/v1/accounts/{id}      - Delete account
 * - GET    /api/v1/accounts/types     - Get account types
 */
class AccountController extends Controller
{
    /**
     * List all accounts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::query()
            ->with('parent:id,code,name')
            ->when($request->boolean('active_only', true), fn($q) => $q->active())
            ->when($request->input('type'), fn($q, $type) => $q->where('type', $type))
            ->when($request->boolean('postable_only'), fn($q) => $q->postable())
            ->when($request->boolean('tree'), fn($q) => $q->root()->with('descendants'));

        // Order by code for a proper chart of accounts view
        $accounts = $query->orderBy('code')->get();

        return response()->json([
            'success' => true,
            'data' => AccountResource::collection($accounts),
            'meta' => [
                'total' => $accounts->count(),
                'types' => AccountType::options(),
            ],
        ]);
    }

    /**
     * Create a new account
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => new AccountResource($account),
        ], 201);
    }

    /**
     * Show a single account
     */
    public function show(Account $account): JsonResponse
    {
        $account->load(['parent:id,code,name', 'children:id,code,name,parent_id']);

        return response()->json([
            'success' => true,
            'data' => new AccountResource($account),
        ]);
    }

    /**
     * Update an account
     */
    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        // System accounts have limited editability
        if ($account->is_system) {
            $allowed = ['name', 'description', 'is_active'];
            $data = $request->only($allowed);
        } else {
            $data = $request->validated();
        }

        $account->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'data' => new AccountResource($account->fresh()),
        ]);
    }

    /**
     * Delete an account
     */
    public function destroy(Account $account): JsonResponse
    {
        // Cannot delete system accounts
        if ($account->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'System accounts cannot be deleted',
            ], 403);
        }

        // Cannot delete accounts with children
        if ($account->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with sub-accounts',
            ], 422);
        }

        // Cannot delete accounts with transactions
        if ($account->journalLines()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with transactions. Deactivate it instead.',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    /**
     * Get account types
     */
    public function types(): JsonResponse
    {
        $types = collect(AccountType::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label(),
                'normal_balance' => $type->normalBalance(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
