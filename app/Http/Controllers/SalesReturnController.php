<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sales\Models\SalesReturn;
use Modules\Sales\Models\Customer;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\Product;
use Modules\Sales\Enums\SalesReturnStatus;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index()
    {
        $returns = SalesReturn::with(['customer', 'salesInvoice'])
            ->latest()
            ->paginate(15);

        return view('sales.returns.index', compact('returns'));
    }

    public function create()
    {
        $customers = Customer::all();
        $warehouses = Warehouse::all();
        // Products will serve as a lookup for the frontend
        $products = Product::select('id', 'name', 'code', 'selling_price', 'tax_rate')->get();

        return view('sales.returns.create', compact('customers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'return_date' => 'required|date',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $return = SalesReturn::create([
                'customer_id' => $validated['customer_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'sales_invoice_id' => $validated['sales_invoice_id'] ?? null,
                'return_date' => $validated['return_date'],
                'status' => SalesReturnStatus::DRAFT,
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0;
            $taxTotal = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $tax = 0; // Ideally fetch from product or frontend if passed

                $return->lines()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'tax_amount' => $tax,
                    'item_condition' => $item['condition'] ?? 'resalable',
                ]);

                $subtotal += $lineTotal;
                $taxTotal += $tax;
            }

            $return->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
            ]);
        });

        return redirect()->route('sales-returns.index')
            ->with('success', 'تم إنشاء مرتجع المبيعات بنجاح');
    }

    public function show(SalesReturn $salesReturn)
    {
        $salesReturn->load(['customer', 'lines.product', 'warehouse']);
        return view('sales.returns.show', compact('salesReturn'));
    }

    public function approve(SalesReturn $salesReturn, \Modules\Inventory\Services\InventoryService $inventoryService, \Modules\Accounting\Services\JournalService $journalService)
    {
        if ($salesReturn->status !== SalesReturnStatus::DRAFT) {
            return back()->with('error', 'هذا المرتجع معتمد بالفعل أو تم إلغاؤه');
        }

        DB::transaction(function () use ($salesReturn, $inventoryService, $journalService) {
            // Update status
            $salesReturn->update([
                'status' => SalesReturnStatus::APPROVED,
                'approved_by' => auth()->id(),
            ]);

            // 1. Inventory Side (Stock + COGS reversal)
            // InventoryService::addStock handles the Journal for Inventory vs COGS automatically.
            foreach ($salesReturn->lines as $line) {
                if ($line->item_condition === 'resalable') {
                    $inventoryService->addStock(
                        product: $line->product,
                        warehouse: $salesReturn->warehouse,
                        quantity: $line->quantity,
                        unitCost: $line->unit_price,
                        type: \Modules\Inventory\Enums\MovementType::RETURN_IN,
                        reference: $salesReturn->return_number,
                        notes: 'Sales Return: ' . $salesReturn->return_number,
                        sourceType: SalesReturn::class,
                        sourceId: $salesReturn->id
                    );
                }
            }

            // 2. Financial Side (AR + Revenue reversal)
            // Reverse of Invoice: Dr Sales (or Sales Returns), Dr Tax, Cr Accounts Receivable

            $arAccount = \Modules\Accounting\Models\Account::where('code', '1201')->first(); // Receivables
            $salesReturnAccount = \Modules\Accounting\Models\Account::where('code', '4102')->first()
                ?? \Modules\Accounting\Models\Account::where('code', '4101')->first(); // Sales Returns or Sales Revenue
            $taxAccount = \Modules\Accounting\Models\Account::where('code', '2105')->first(); // Tax Payable

            if ($arAccount && $salesReturnAccount) {
                $lines = [
                    // Debit Sales/Returns (Reduce Income)
                    [
                        'account_id' => $salesReturnAccount->id,
                        'debit' => $salesReturn->subtotal,
                        'credit' => 0,
                        'description' => 'Returns Value'
                    ],
                    // Credit Customer (Reduce Receivable) - Total Amount
                    [
                        'account_id' => $arAccount->id,
                        'debit' => 0,
                        'credit' => $salesReturn->total_amount,
                        'description' => 'Credit to Customer ' . $salesReturn->customer->name
                    ],
                ];

                // Debit Tax (Reduce Tax Liability)
                if ($salesReturn->tax_amount > 0 && $taxAccount) {
                    $lines[] = [
                        'account_id' => $taxAccount->id,
                        'debit' => $salesReturn->tax_amount,
                        'credit' => 0,
                        'description' => 'Tax Reversal'
                    ];
                }

                $entry = $journalService->create([
                    'entry_date' => $salesReturn->return_date,
                    'reference' => $salesReturn->return_number,
                    'description' => "Sales Return from {$salesReturn->customer->name}",
                    'source_type' => SalesReturn::class,
                    'source_id' => $salesReturn->id,
                ], $lines);

                $journalService->post($entry);
            }
        });

        return redirect()->route('sales-returns.show', $salesReturn)
            ->with('success', 'تم اعتماد المرتجع: تم تحديث المخزون والحسابات بنجاح');
    }

    // API / AJAX Helpers
    public function getCustomerInvoices(Customer $customer)
    {
        $invoices = \Modules\Sales\Models\SalesInvoice::where('customer_id', $customer->id)
            ->where('status', '!=', 'cancelled')
            ->select('id', 'invoice_number', 'invoice_date', 'total', 'status')
            ->latest()
            ->get();

        return response()->json($invoices);
    }

    public function getInvoiceLines(\Modules\Sales\Models\SalesInvoice $invoice)
    {
        $lines = $invoice->lines()->with('product:id,name,code,selling_price')->get()->map(function ($line) {
            return [
                'product_id' => $line->product_id,
                'product_name' => $line->product->name,
                'product_code' => $line->product->code,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'remaining' => $line->quantity - 0, // In future: substract already returned qty
            ];
        });

        return response()->json($lines);
    }
}
