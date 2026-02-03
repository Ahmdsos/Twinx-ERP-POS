<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Sales\Services\SalesService;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {
    }

    /**
     * Display the Mission Control dashboard
     */
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['customer', 'salesOrder', 'salesInvoice', 'warehouse'])
            ->where(function ($q) {
                // Active Missions
                $q->whereIn('status', [DeliveryStatus::SHIPPED, DeliveryStatus::READY])
                    // OR Settled Today (Persistence Rule)
                    ->orWhere(function ($sq) {
                    $sq->whereIn('status', [DeliveryStatus::DELIVERED, DeliveryStatus::RETURNED])
                        ->whereDate('updated_at', now());
                });
            })
            ->orderByRaw("CASE WHEN status IN ('shipped', 'ready') THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc');

        if ($request->filled('driver_name')) {
            $query->where('driver_name', 'like', '%' . $request->driver_name . '%');
        }

        $missions = $query->paginate(15);

        $stats = [
            'active' => DeliveryOrder::whereIn('status', [DeliveryStatus::SHIPPED, DeliveryStatus::READY])->count(),
            'returned' => DeliveryOrder::where('status', DeliveryStatus::RETURNED)->whereDate('updated_at', now())->count(),
            'delivered' => DeliveryOrder::where('status', DeliveryStatus::DELIVERED)->whereDate('updated_at', now())->count(),
        ];

        return view('sales.deliveries.mission-control', compact('missions', 'stats'));
    }

    /**
     * Settle a mission (Success or Return)
     */
    public function settle(Request $request, DeliveryOrder $delivery)
    {
        $request->validate([
            'status' => 'required|in:delivered,returned',
            'notes' => 'nullable|string|max:500',
            'collected_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            // This is where the magic happens (Logistics Settlement Engine)
            $this->salesService->settleDeliveryMission(
                $delivery,
                $request->status,
                $request->only(['notes', 'collected_amount'])
            );

            return response()->json([
                'success' => true,
                'message' => 'تمت عملية التسوية بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشلت عملية التسوية: ' . $e->getMessage()
            ], 500);
        }
    }
}
