<?php

namespace Modules\Accounting\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\StoreJournalEntryRequest;
use Modules\Accounting\Http\Resources\JournalEntryResource;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Enums\JournalStatus;
use Modules\Accounting\Exceptions\UnbalancedJournalException;

/**
 * JournalEntryController - API for Journal Entry management
 * 
 * Endpoints:
 * - GET    /api/v1/journal-entries              - List entries
 * - POST   /api/v1/journal-entries              - Create entry
 * - GET    /api/v1/journal-entries/{id}         - Show entry
 * - DELETE /api/v1/journal-entries/{id}         - Delete draft entry
 * - POST   /api/v1/journal-entries/{id}/post    - Post entry
 * - POST   /api/v1/journal-entries/{id}/reverse - Reverse entry
 */
class JournalEntryController extends Controller
{
    public function __construct(
        protected JournalService $journalService
    ) {
    }

    /**
     * List journal entries
     */
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntry::query()
            ->with(['lines.account:id,code,name', 'creator:id,name'])
            ->when($request->input('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->input('start_date'), function ($q, $date) {
                $q->where('entry_date', '>=', $date);
            })
            ->when($request->input('end_date'), function ($q, $date) {
                $q->where('entry_date', '<=', $date);
            })
            ->when($request->input('reference'), function ($q, $ref) {
                $q->where('reference', 'like', "%{$ref}%");
            })
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        $perPage = min($request->input('per_page', 25), 100);
        $entries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => JournalEntryResource::collection($entries),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
            ],
        ]);
    }

    /**
     * Create a new journal entry
     */
    public function store(StoreJournalEntryRequest $request): JsonResponse
    {
        try {
            $entry = $this->journalService->create(
                $request->only(['entry_date', 'reference', 'description']),
                $request->input('lines')
            );

            return response()->json([
                'success' => true,
                'message' => 'Journal entry created successfully',
                'data' => new JournalEntryResource($entry->load('lines.account')),
            ], 201);
        } catch (UnbalancedJournalException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'total_debit' => $e->getTotalDebit(),
                    'total_credit' => $e->getTotalCredit(),
                    'difference' => $e->getDifference(),
                ],
            ], 422);
        }
    }

    /**
     * Show a single journal entry
     */
    public function show(JournalEntry $journalEntry): JsonResponse
    {
        $journalEntry->load(['lines.account', 'fiscalYear', 'creator', 'postedByUser']);

        return response()->json([
            'success' => true,
            'data' => new JournalEntryResource($journalEntry),
        ]);
    }

    /**
     * Delete a draft journal entry
     */
    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        if (!$journalEntry->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft entries can be deleted',
            ], 422);
        }

        $this->journalService->delete($journalEntry);

        return response()->json([
            'success' => true,
            'message' => 'Journal entry deleted successfully',
        ]);
    }

    /**
     * Post a journal entry (make it affect ledger)
     */
    public function post(JournalEntry $journalEntry): JsonResponse
    {
        try {
            $entry = $this->journalService->post($journalEntry);

            return response()->json([
                'success' => true,
                'message' => 'Journal entry posted successfully',
                'data' => new JournalEntryResource($entry->load('lines.account')),
            ]);
        } catch (UnbalancedJournalException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reverse a posted journal entry
     */
    public function reverse(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        try {
            $reversalEntry = $this->journalService->reverse(
                $journalEntry,
                $request->input('description')
            );

            return response()->json([
                'success' => true,
                'message' => 'Journal entry reversed successfully',
                'data' => [
                    'original_entry' => new JournalEntryResource($journalEntry->fresh()),
                    'reversal_entry' => new JournalEntryResource($reversalEntry->load('lines.account')),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
