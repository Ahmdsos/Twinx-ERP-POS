<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Models\DeliveryOrderLine;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Models\SalesOrderLine;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Sales\Enums\SalesOrderStatus;
use Modules\Sales\Services\SalesService;
use Modules\Inventory\Models\Warehouse;

/**
 * DeliveryOrderController - Manages Delivery Order UI operations
 * 
 * Handles:
 * - Create delivery from sales order
 * - Ship deliveries
 * - Mark as delivered
 */
class DeliveryOrderController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {
    }

    /**
     * Display list of delivery orders
     */
    public function index(Request $request)
    {
        $query = DeliveryOrder::with(['salesOrder', 'customer', 'warehouse'])
            ->orderByDesc('delivery_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by sales order
        if ($request->filled('sales_order_id')) {
            $query->where('sales_order_id', $request->sales_order_id);
        }

        // Filter by date
        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        $deliveries = $query->paginate(20);
        $statuses = DeliveryStatus::cases();

        return view('sales.deliveries.index', compact('deliveries', 'statuses'));
    }

    /**
     * Show form for creating delivery from sales order
     */
    public function create(Request $request)
    {
        $salesOrder = null;

        if ($request->filled('sales_order_id')) {
            $salesOrder = SalesOrder::with(['customer', 'warehouse', 'lines.product.unit'])
                ->findOrFail($request->sales_order_id);

            // Check if SO can be delivered
            if (!$salesOrder->canDeliver()) {
                return redirect()->route('sales-orders.show', $salesOrder)
                    ->with('error', 'لا يمكن إنشاء تسليم لهذا الأمر - الحالة: ' . $salesOrder->status->label());
            }
        }

        $salesOrders = SalesOrder::awaitingDelivery()
            ->with('customer')
            ->orderByDesc('order_date')
            ->get();

        $warehouses = Warehouse::where('is_active', true)->get();

        return view('sales.deliveries.create', compact('salesOrder', 'salesOrders', 'warehouses'));
    }

    /**
     * Store new delivery order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_date' => 'required|date',
            'shipping_address' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'driver_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            // Line quantities
            'lines' => 'required|array',
            'lines.*.sales_order_line_id' => 'required|exists:sales_order_lines,id',
            'lines.*.quantity' => 'required|numeric|min:0',
        ]);

        $salesOrder = SalesOrder::findOrFail($validated['sales_order_id']);
        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        // Filter lines with quantity > 0
        $itemsToDeliver = [];
        foreach ($validated['lines'] as $lineData) {
            if ($lineData['quantity'] > 0) {
                $soLine = SalesOrderLine::find($lineData['sales_order_line_id']);
                if ($soLine) {
                    $itemsToDeliver[] = [
                        'sales_order_line_id' => $soLine->id,
                        'product_id' => $soLine->product_id,
                        'quantity' => $lineData['quantity'],
                    ];
                }
            }
        }

        if (empty($itemsToDeliver)) {
            return back()->with('error', 'يجب تحديد كمية واحدة على الأقل للتسليم');
        }

        try {
            $deliveryOrder = $this->salesService->createDelivery(
                so: $salesOrder,
                warehouse: $warehouse,
                itemsToDeliver: $itemsToDeliver,
                shippingMethod: $validated['shipping_method'] ?? null,
                notes: $validated['notes'] ?? null
            );
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في إنشاء أمر التسليم: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('deliveries.show', $deliveryOrder)
            ->with('success', 'تم إنشاء أمر التسليم بنجاح: ' . $deliveryOrder->do_number);
    }

    /**
     * Display delivery order details
     */
    public function show(DeliveryOrder $delivery)
    {
        $delivery->load([
            'salesOrder.customer',
            'warehouse',
            'lines.product.unit',
        ]);

        return view('sales.deliveries.show', compact('delivery'));
    }

    /**
     * Ship the delivery order
     */
    public function ship(Request $request, DeliveryOrder $delivery)
    {
        if ($delivery->status !== DeliveryStatus::READY) {
            return back()->with('error', 'يمكن شحن الطلبات الجاهزة فقط');
        }

        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $delivery->update([
            'status' => DeliveryStatus::SHIPPED,
            'shipped_date' => now(),
            'driver_name' => $validated['driver_name'] ?? null,
            'vehicle_number' => $validated['vehicle_number'] ?? null,
            'tracking_number' => $validated['tracking_number'] ?? null,
        ]);

        return back()->with('success', 'تم شحن الطلب بنجاح');
    }

    /**
     * Mark delivery as completed
     */
    public function complete(DeliveryOrder $delivery)
    {
        if (!in_array($delivery->status, [DeliveryStatus::READY, DeliveryStatus::SHIPPED])) {
            return back()->with('error', 'لا يمكن إتمام هذا التسليم');
        }

        $delivery->update([
            'status' => DeliveryStatus::DELIVERED,
            'delivered_by' => auth()->id(),
        ]);

        // Update SO delivery status
        $delivery->salesOrder->updateDeliveryStatus();

        return back()->with('success', 'تم تسليم الطلب بنجاح');
    }

    /**
     * Cancel delivery order
     */
    public function cancel(DeliveryOrder $delivery)
    {
        if ($delivery->status === DeliveryStatus::DELIVERED) {
            return back()->with('error', 'لا يمكن إلغاء طلب تم تسليمه');
        }

        $delivery->update(['status' => DeliveryStatus::CANCELLED]);

        return back()->with('success', 'تم إلغاء أمر التسليم');
    }
}
