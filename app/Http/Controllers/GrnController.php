<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\Grn;
use Modules\Purchasing\Models\GrnLine;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Models\PurchaseOrderLine;
use Modules\Purchasing\Enums\GrnStatus;
use Modules\Purchasing\Services\PurchasingService;
use Modules\Inventory\Models\Warehouse;

/**
 * GrnController - Manages Goods Received Notes UI operations
 * 
 * Handles:
 * - Create GRN from Purchase Order
 * - View GRN details
 * - Complete/Cancel GRN
 */
class GrnController extends Controller
{
    protected PurchasingService $purchasingService;

    public function __construct(PurchasingService $purchasingService)
    {
        $this->purchasingService = $purchasingService;
    }

    /**
     * Display list of GRNs
     */
    public function index(Request $request)
    {
        $query = Grn::with(['purchaseOrder', 'supplier', 'warehouse', 'receiver'])
            ->orderByDesc('received_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by PO
        if ($request->filled('purchase_order_id')) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        // Search by GRN number
        if ($request->filled('search')) {
            $query->where('grn_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date
        if ($request->filled('from_date')) {
            $query->whereDate('received_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('received_date', '<=', $request->to_date);
        }

        $grns = $query->paginate(20);
        $statuses = GrnStatus::cases();

        return view('purchasing.grns.index', compact('grns', 'statuses'));
    }

    /**
     * Show form for creating new GRN from a Purchase Order
     */
    public function create(Request $request)
    {
        // If PO ID is provided, load it
        $purchaseOrder = null;
        if ($request->filled('purchase_order_id')) {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse', 'lines.product.unit'])
                ->find($request->purchase_order_id);

            if (!$purchaseOrder || !$purchaseOrder->canReceive()) {
                return redirect()->route('grns.index')
                    ->with('error', 'أمر الشراء غير متاح للاستلام');
            }
        }

        // Get POs awaiting receipt
        $purchaseOrders = PurchaseOrder::awaitingReceipt()
            ->with('supplier')
            ->orderByDesc('order_date')
            ->get();

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('purchasing.grns.create', compact('purchaseOrder', 'purchaseOrders', 'warehouses'));
    }

    /**
     * Store new GRN
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'received_date' => 'required|date',
            'supplier_delivery_note' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            // Line items
            'lines' => 'required|array|min:1',
            'lines.*.purchase_order_line_id' => 'required|exists:purchase_order_lines,id',
            'lines.*.quantity' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        if (!$po->canReceive()) {
            return back()->with('error', 'أمر الشراء غير متاح للاستلام');
        }

        // Prepare items to receive
        $itemsToReceive = [];
        foreach ($validated['lines'] as $line) {
            if ($line['quantity'] > 0) {
                $poLine = PurchaseOrderLine::find($line['purchase_order_line_id']);
                if ($poLine) {
                    $itemsToReceive[] = [
                        'purchase_order_line_id' => $poLine->id,
                        'product_id' => $poLine->product_id,
                        'quantity' => $line['quantity'],
                        'unit_price' => $poLine->unit_price,
                    ];
                }
            }
        }

        if (empty($itemsToReceive)) {
            return back()->with('error', 'يجب إدخال كمية مستلمة واحدة على الأقل');
        }

        try {
            $grn = $this->purchasingService->receiveGoods(
                $po,
                $warehouse,
                $itemsToReceive,
                $validated['supplier_delivery_note'] ?? null,
                $validated['notes'] ?? null
            );

            return redirect()->route('grns.show', $grn)
                ->with('success', 'تم إنشاء سند استلام البضاعة: ' . $grn->grn_number);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display GRN details
     */
    public function show(Grn $grn)
    {
        $grn->load([
            'purchaseOrder',
            'supplier',
            'warehouse',
            'lines.product.unit',
            'receiver',
        ]);

        return view('purchasing.grns.show', compact('grn'));
    }

    /**
     * Cancel GRN (only drafts)
     */
    public function cancel(Grn $grn)
    {
        if (!$grn->canEdit()) {
            return back()->with('error', 'لا يمكن إلغاء هذا السند');
        }

        $grn->update(['status' => GrnStatus::CANCELLED]);

        return back()->with('success', 'تم إلغاء سند الاستلام');
    }
}
