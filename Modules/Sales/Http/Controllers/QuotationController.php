<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Modules\Sales\Models\Quotation;
use Modules\Sales\Models\QuotationLine;
use Modules\Sales\Models\Customer;
use Modules\Sales\Enums\QuotationStatus;
use Modules\Sales\Services\SalesService;
use Modules\Inventory\Models\Product;

/**
 * QuotationController - Manages Quotation UI operations
 * 
 * Handles:
 * - Create/Edit quotations
 * - Send to customer
 * - Accept/Reject
 * - Convert to Sales Order
 */
class QuotationController extends Controller
{
    protected SalesService $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Display list of quotations
     */
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'creator'])
            ->orderByDesc('quotation_date')
            ->orderByDesc('id');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Search by quotation number
        if ($request->filled('search')) {
            $query->where('quotation_number', 'like', '%' . $request->search . '%');
        }

        // Filter by date
        if ($request->filled('from_date')) {
            $query->whereDate('quotation_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('quotation_date', '<=', $request->to_date);
        }

        // Show expired only
        if ($request->boolean('expired_only')) {
            $query->expired();
        }

        $quotations = $query->paginate(20);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $statuses = QuotationStatus::cases();

        // Stats
        $stats = [
            'pending' => Quotation::pending()->count(),
            'accepted' => Quotation::where('status', QuotationStatus::ACCEPTED)->count(),
            'total_value' => Quotation::pending()->sum('total'),
        ];

        return view('sales.quotations.index', compact('quotations', 'customers', 'statuses', 'stats'));
    }

    /**
     * Show form for creating a new quotation
     */
    public function create(Request $request)
    {
        // If customer is pre-selected
        $selectedCustomer = null;
        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        }

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->where('is_sellable', true)
            ->with('unit')
            ->orderBy('name')
            ->get();

        // Tax settings from database (stored as percent, e.g., 20 for 20%)
        $taxRatePercent = (float) \App\Models\Setting::getValue('default_tax_rate', 14);
        $taxRate = $taxRatePercent / 100; // Convert to decimal for JS

        return view('sales.quotations.create', compact('customers', 'products', 'selectedCustomer', 'taxRate', 'taxRatePercent'));
    }

    /**
     * Store a new quotation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required_without:target_customer_type|array|nullable',
            'customer_id.*' => 'exists:customers,id',
            'target_customer_type' => 'required_without:customer_id|nullable|string',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:quotation_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $customerIds = $validated['customer_id'] ?? [];
            $primaryCustomerId = !empty($customerIds) ? $customerIds[0] : null;

            $quotationData = $validated;
            $quotationData['customer_id'] = $primaryCustomerId;

            $quotation = $this->salesService->createQuotation(
                $quotationData,
                $validated['lines']
            );

            // Sync multi-customers after creation
            if (!empty($customerIds)) {
                $quotation->customers()->sync($customerIds);
            }

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'تم إنشاء عرض السعر: ' . $quotation->quotation_number);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Display quotation details
     */
    public function show(Quotation $quotation)
    {
        $quotation->load([
            'customer',
            'lines.product.unit',
            'creator',
            'salesOrder',
        ]);

        return view('sales.quotations.show', compact('quotation'));
    }

    /**
     * Show form for editing quotation
     */
    public function edit(Quotation $quotation)
    {
        if (!$quotation->status->canEdit()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'لا يمكن تعديل عرض سعر في حالة: ' . $quotation->status->label());
        }

        $quotation->load(['customer', 'lines.product.unit']);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->where('is_sellable', true)
            ->with('unit')
            ->orderBy('name')
            ->get();

        // Tax settings from database (stored as percent, e.g., 20 for 20%)
        $taxRatePercent = (float) \App\Models\Setting::getValue('default_tax_rate', 14);
        $taxRate = $taxRatePercent / 100; // Convert to decimal for JS

        return view('sales.quotations.edit', compact('quotation', 'customers', 'products', 'taxRate', 'taxRatePercent'));
    }

    /**
     * Update quotation
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (!$quotation->status->canEdit()) {
            return back()->with('error', 'لا يمكن تعديل عرض سعر في هذه الحالة');
        }

        $validated = $request->validate([
            'customer_id' => 'required_without:target_customer_type|array|nullable',
            'customer_id.*' => 'exists:customers,id',
            'target_customer_type' => 'required_without:customer_id|nullable|string',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:quotation_date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            // Update header
            $customerIds = $validated['customer_id'] ?? [];
            $primaryCustomerId = !empty($customerIds) ? $customerIds[0] : null;

            // Update main record
            $quotation->update([
                'customer_id' => $primaryCustomerId,
                'target_customer_type' => $validated['target_customer_type'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'discount_amount' => $validated['discount_amount'] ?? 0,
            ]);

            // Sync multi-customers
            $quotation->customers()->sync($customerIds);

            // Delete old lines and recreate
            $quotation->lines()->delete();

            foreach ($validated['lines'] as $lineData) {
                $product = Product::find($lineData['product_id']);
                $quotation->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'],
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                    'tax_percent' => $product->tax_rate ?? 0,
                    'unit_id' => $product->unit_id,
                    'description' => $product->name,
                ]);
            }

            // Recalculate totals
            $quotation->calculateTotals();

            return redirect()->route('quotations.show', $quotation)
                ->with('success', 'تم تحديث عرض السعر');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    /**
     * Mark quotation as sent
     */
    public function send(Quotation $quotation)
    {
        if ($quotation->markAsSent()) {
            return back()->with('success', 'تم تحديث حالة العرض إلى "مرسل"');
        }

        return back()->with('error', 'لا يمكن إرسال عرض السعر في هذه الحالة');
    }

    /**
     * Accept quotation
     */
    public function accept(Quotation $quotation)
    {
        if ($quotation->accept()) {
            return back()->with('success', 'تم قبول عرض السعر');
        }

        return back()->with('error', 'لا يمكن قبول عرض السعر في هذه الحالة');
    }

    /**
     * Reject quotation
     */
    public function reject(Quotation $quotation)
    {
        if ($quotation->reject()) {
            return back()->with('success', 'تم رفض عرض السعر');
        }

        return back()->with('error', 'لا يمكن رفض عرض السعر في هذه الحالة');
    }

    /**
     * Convert quotation to sales order
     */
    public function convert(Quotation $quotation)
    {
        try {
            $salesOrder = $this->salesService->convertQuotationToOrder($quotation);

            return redirect()->route('sales-orders.show', $salesOrder)
                ->with('success', 'تم تحويل عرض السعر إلى أمر بيع: ' . $salesOrder->order_number);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Print quotation
     */
    public function print(Quotation $quotation)
    {
        $quotation->load([
            'customer',
            'lines.product.unit',
        ]);

        return view('sales.quotations.print', compact('quotation'));
    }

    /**
     * Delete quotation
     */
    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === QuotationStatus::CONVERTED || $quotation->status === QuotationStatus::ACCEPTED) {
            return back()->with('error', 'لا يمكن حذف عرض سعر تم قبوله أو تحويله');
        }

        try {
            // Delete related lines first if not Cascade delete in DB
            $quotation->lines()->delete();
            $quotation->delete();

            return redirect()->route('quotations.index')
                ->with('success', 'تم حذف عرض السعر بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    /**
     * Expire/Close quotation manually
     */
    public function expire(Quotation $quotation)
    {
        if (in_array($quotation->status, [QuotationStatus::CONVERTED, QuotationStatus::EXPIRED])) {
            return back()->with('error', 'هذا العرض منتهي بالفعل أو تم تحويله');
        }

        try {
            $quotation->update(['status' => QuotationStatus::EXPIRED]);

            return back()->with('success', 'تم إغلاق عرض السعر بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ أثناء الإغلاق: ' . $e->getMessage());
        }
    }
}
