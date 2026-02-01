<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Purchasing\Models\PurchaseReturn;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\Supplier;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Enums\MovementType;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'invoice'])->orderByDesc('return_date');

        if ($request->filled('search')) {
            $query->where('return_number', 'like', '%' . $request->search . '%');
        }

        $returns = $query->paginate(20);

        $stats = [
            'total_returns' => PurchaseReturn::sum('total_amount'),
            'this_month' => PurchaseReturn::whereMonth('return_date', now()->month)->sum('total_amount'),
        ];

        return view('purchasing.returns.index', compact('returns', 'stats'));
    }

    public function create(Request $request)
    {
        $invoices = PurchaseInvoice::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('invoice_date')
            ->get();

        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = PurchaseInvoice::with(['lines.product', 'supplier'])->find($request->invoice_id);
        }

        return view('purchasing.returns.create', compact('invoices', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:purchase_invoices,id',
            'return_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $invoice = PurchaseInvoice::findOrFail($request->invoice_id);

                // 1. Create Return Record
                $return = PurchaseReturn::create([
                    'supplier_id' => $invoice->supplier_id,
                    'purchase_invoice_id' => $invoice->id,
                    'return_date' => $request->return_date,
                    'status' => 'approved',
                    'notes' => $request->notes,
                    'created_by' => auth()->id(),
                ]);

                $totalAmount = 0;
                $warehouse = Warehouse::first(); // Default for now, ideally selected

                // 2. Process Items
                foreach ($request->items as $item) {
                    if ($item['quantity'] <= 0)
                        continue;

                    $product = \Modules\Inventory\Models\Product::find($item['product_id']);
                    $lineTotal = $item['quantity'] * $item['price'];
                    $totalAmount += $lineTotal;

                    // Create Line
                    $return->lines()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'line_total' => $lineTotal,
                    ]);

                    // Update Stock (OUT)
                    $this->inventoryService->addStock(
                        $product,
                        $warehouse,
                        -$item['quantity'], // Negative for return
                        $item['price'],
                        MovementType::RETURN_OUT,
                        $return->return_number,
                        'Purchase Return',
                        PurchaseReturn::class,
                        $return->id
                    );
                }

                $return->update([
                    'total_amount' => $totalAmount,
                    'subtotal' => $totalAmount, // Assuming no tax calc for simplicity yet
                ]);

                // TODO: Journal Entry (Debit Supplier, Credit Inventory)
            });

            return redirect()->route('purchase-returns.index')
                ->with('success', 'تم تسجيل مرتجع الشراء بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['lines.product', 'supplier', 'invoice']);
        return view('purchasing.returns.show', compact('purchaseReturn'));
    }
}
