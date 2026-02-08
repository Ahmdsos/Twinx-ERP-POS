<?php

namespace Modules\Sales\Services;

use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Sales\Models\CustomerPayment;
use Modules\Sales\Models\CustomerPaymentAllocation;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Models\DeliveryOrderLine;
use Modules\Sales\Enums\SalesInvoiceStatus;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Enums\MovementType;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Models\Account;
use Modules\Core\Traits\HasTaxCalculations;
use Modules\HR\Models\DeliveryDriver;
use App\Models\Setting;

/**
 * POSService
 * Unified service for POS checkout operations (Vertical Truth Enforcement)
 */
class POSService
{
    use HasTaxCalculations;

    public function __construct(
        protected InventoryService $inventoryService,
        protected JournalService $journalService,
        protected SalesService $salesService
    ) {
    }

    /**
     * Handle the complete POS checkout workflow
     */
    public function checkout(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {
            $activeShift = \App\Models\PosShift::getActiveShift();

            // 1. Process Items & Calculate Totals
            $items = $data['items'];
            $invoiceLinesData = [];
            $subtotal = 0;
            $totalTax = 0;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                $calc = $this->calculateLineTax(
                    (float) $item['quantity'],
                    (float) $item['price'],
                    (float) ($item['discount'] ?? 0),
                    $product->tax_rate
                );

                $subtotal += $calc['subtotal'];
                $totalTax += $calc['tax_amount'];

                $invoiceLinesData[] = array_merge($calc, [
                    'product_id' => $product->id,
                    'product_instance' => $product,
                    'description' => $product->name,
                ]);
            }

            $globalDiscount = (float) ($data['discount'] ?? 0);
            $deliveryFee = (float) ($data['delivery_fee'] ?? 0);

            // F-07 Validation: Prevent 100%+ discount
            if ($globalDiscount > ($subtotal + $totalTax)) {
                throw new \RuntimeException("قيمة الخصم لا يمكن أن تتجاوز إجمالي الفاتورة");
            }

            // MATH TRUTH: Total = Net Subtotal + Total Tax - Global Discount + Delivery Fee
            $total = (float) round($subtotal + $totalTax - $globalDiscount + $deliveryFee, 2);

            // Calculate total paid across all payment methods
            $amountPaid = collect($data['payments'])->sum('amount');
            $balanceDue = max(0, (float) round($total - $amountPaid, 2));

            // 2. Resolve Customer
            $customerId = $data['customer_id'] ?? $this->resolveWalkInCustomer()->id;
            $customer = \Modules\Sales\Models\Customer::find($customerId);

            // Phase 2.3: Credit limit enforcement (only for credit payments)
            $hasCredit = collect($data['payments'])->contains(fn($p) => $p['method'] === 'credit');
            if ($hasCredit && $customer) {
                // Walk-in customers cannot have credit
                if ($customer->code === 'WALK-IN') {
                    throw new \RuntimeException("لا يمكن البيع العميل 'نقدي' بنظام الآجل");
                }

                if (!$customer->canPlaceOrder($total)) {
                    $reason = $customer->getOrderBlockReason($total);
                    throw new \RuntimeException($reason ?? "العميل تجاوز حد الائتمان المسموح");
                }
            }

            // Phase 2.3: Stock availability validation
            $targetWarehouseId = $data['warehouse_id'] ?? $activeShift->warehouse_id ?? 1;
            foreach ($items as $item) {
                $stock = \Modules\Inventory\Models\ProductStock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $targetWarehouseId)
                    ->first();
                $available = (float) ($stock ? $stock->quantity : 0);
                $reqQty = (float) $item['quantity'];

                if ($available < $reqQty) {
                    $product = Product::find($item['product_id']);
                    throw new \RuntimeException("الكمية المتاحة من ({$product->name}) هي {$available} فقط في هذا المخزن");
                }
            }

            // 3. Create Sales Invoice
            $invoice = SalesInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $customerId,
                'invoice_date' => now(),
                'due_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $globalDiscount,
                'tax_amount' => $totalTax,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'paid_amount' => $amountPaid,
                'balance_due' => $balanceDue,
                'status' => $balanceDue > 0 ? SalesInvoiceStatus::PARTIAL : SalesInvoiceStatus::PAID,
                'is_delivery' => $data['is_delivery'] ?? false,
                'driver_id' => $data['driver_id'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? Warehouse::first()?->id ?? 1,
                'pos_shift_id' => $activeShift->id,
                'created_by' => $data['cashier_id'] ?? auth()->id(), // Phase 3: Cashier Assignment
            ]);

            // Track stats in shift - Split awareness
            if ($activeShift) {
                foreach ($data['payments'] as $payment) {
                    $activeShift->incrementSales($payment['amount'], $payment['method']);
                }
            }

            // 4. Create Lines & Handle Stock
            foreach ($invoiceLinesData as $lineData) {
                $productHost = $lineData['product_instance'];
                unset($lineData['product_instance']);

                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $lineData['product_id'],
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price_net'],
                    'discount_amount' => $lineData['discount_amount'],
                    'tax_percent' => $lineData['tax_percent'],
                    'tax_amount' => $lineData['tax_amount'],
                    'line_total' => $lineData['line_total'],
                ]);

                if ($lineData['quantity'] > 0) {
                    $this->inventoryService->removeStock(
                        $productHost,
                        Warehouse::find($invoice->warehouse_id),
                        $lineData['quantity'],
                        MovementType::SALE,
                        $invoice->invoice_number,
                        'POS Sale',
                        SalesInvoice::class,
                        $invoice->id
                    );
                }
            }

            // 5. Handle Delivery Integration
            if ($invoice->is_delivery) {
                $this->createDeliveryOrder($invoice, $data);
            }

            // 6. Record Payments (Split support)
            foreach ($data['payments'] as $paymentData) {
                if ($paymentData['amount'] > 0) {
                    $this->recordPayment($invoice, $paymentData);
                }
            }

            // 7. Create Journal Entry (Split support)
            $this->createInvoiceJournalEntry($invoice, $data['payments']);

            // 8. Track Stats in Shift (Split Awareness)
            // Added back to ensure X-Report works correctly
            if ($activeShift) {
                $activeShift->incrementTransaction();
                foreach ($data['payments'] as $payment) {
                    $activeShift->incrementSales((float) $payment['amount'], $payment['method']);
                }
            }

            return $invoice;
        });
    }

    /**
     * Resolve or Create Walk-in Customer
     */
    protected function resolveWalkInCustomer(): Customer
    {
        return Customer::firstOrCreate(
            ['code' => 'WALK-IN'],
            [
                'name' => 'Walk-in Customer',
                'phone' => 'N/A',
                'is_active' => true,
                'created_by' => 1,
            ]
        );
    }

    /**
     * Generate POS Invoice Number (Sequential within Date)
     */
    protected function generateInvoiceNumber(): string
    {
        $datestamp = now()->format('Ymd');
        $prefix = 'POS-' . $datestamp . '-';

        // Use lockForUpdate() to prevent race conditions in concurrent POS transactions
        $lastInvoice = SalesInvoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create Delivery Order from Invoice
     */
    public function createDeliveryOrder(SalesInvoice $invoice, array $data): void
    {
        // Fix: Use passed data address first, then fallback to customer address
        $shippingAddress = $data['shipping_address'] ?? $invoice->customer->address;

        if (empty($shippingAddress)) {
            throw new \Exception('Shipping address is required for delivery orders.');
        }

        $do = DeliveryOrder::create([
            'sales_invoice_id' => $invoice->id,
            'sales_order_id' => null, // Explicitly null for POS direct sales
            'customer_id' => $invoice->customer_id,
            'warehouse_id' => $invoice->warehouse_id,
            'delivery_date' => now(),
            'status' => DeliveryStatus::READY,
            'shipping_address' => $shippingAddress,
            'driver_id' => $data['driver_id'] ?? null,
            'recipient_name' => $data['recipient_name'] ?? $invoice->customer->name,
            'recipient_phone' => $data['recipient_phone'] ?? $invoice->customer->phone,
            'notes' => ($data['notes'] ?? '') . ' (POS Invoice: ' . $invoice->invoice_number . ')',
        ]);

        foreach ($invoice->lines as $line) {
            DeliveryOrderLine::create([
                'delivery_order_id' => $do->id,
                'product_id' => $line->product_id,
                'quantity' => $line->quantity,
                'unit_cost' => $line->product->cost_price ?? 0,
            ]);
        }

        // Update driver status if assigned
        if (!empty($data['driver_id'])) {
            $driver = DeliveryDriver::find($data['driver_id']);
            if ($driver) {
                $driver->increment('total_deliveries');
                $driver->update(['status' => 'on_delivery']);
            }
        }

        $invoice->update(['delivery_order_id' => $do->id]);
    }

    /**
     * Record Cash/Card Payment
     */
    protected function recordPayment(SalesInvoice $invoice, array $paymentData): void
    {
        $method = $paymentData['method'];
        $amount = (float) $paymentData['amount'];

        // Skip credit or zero payments
        if ($method === 'credit' || $amount <= 0)
            return;

        $defaultCashAccount = Setting::getValue('acc_cash_id', 1);
        $defaultBankAccount = Setting::getValue('acc_bank_id', 2);

        $paymentAccountId = $paymentData['account_id'] ??
            (($method === 'card' || $method === 'bank') ? $defaultBankAccount : $defaultCashAccount);

        $payment = CustomerPayment::create([
            'customer_id' => $invoice->customer_id,
            'payment_account_id' => $paymentAccountId,
            'payment_date' => now(),
            'amount' => $amount,
            'payment_method' => $method,
            'reference' => 'POS-' . $invoice->invoice_number,
            'receipt_number' => 'RCT-' . now()->format('YmdHisv'), // Added milliseconds for uniqueness in split
            'notes' => 'POS ' . ucfirst($method) . ' Payment for ' . $invoice->invoice_number,
            'created_by' => auth()->id() ?? 1,
        ]);

        CustomerPaymentAllocation::create([
            'customer_payment_id' => $payment->id,
            'sales_invoice_id' => $invoice->id,
            'amount' => $amount,
        ]);
    }

    /**
     * Handle POS Sales Return (Audit Finding #10)
     * Transitioning from negative invoices to proper SalesReturn model
     */
    public function salesReturn(array $data): \Modules\Sales\Models\SalesReturn
    {
        return DB::transaction(function () use ($data) {
            $originalInvoice = SalesInvoice::with('lines')->findOrFail($data['invoice_id']);

            // 1. Create Head of Return - Generate proper sequential return number
            $datestamp = now()->format('Ymd');
            $returnPrefix = 'RET-' . $datestamp . '-';

            // Use lockForUpdate() to get last return number safely
            $lastReturn = \Modules\Sales\Models\SalesReturn::where('return_number', 'like', $returnPrefix . '%')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $returnSequence = $lastReturn ? (int) substr($lastReturn->return_number, -4) + 1 : 1;
            $returnNumber = $returnPrefix . str_pad($returnSequence, 4, '0', STR_PAD_LEFT);

            $activeShift = \App\Models\PosShift::getActiveShift();

            $return = \Modules\Sales\Models\SalesReturn::create([
                'return_number' => $returnNumber,
                'sales_invoice_id' => $originalInvoice->id,
                'customer_id' => $originalInvoice->customer_id,
                'warehouse_id' => $originalInvoice->warehouse_id ?? 1,
                'shift_id' => $activeShift?->id, // Audit Link
                'return_date' => now(),
                'status' => \Modules\Sales\Enums\SalesReturnStatus::COMPLETED,
                'reason' => $data['reason'] ?? 'Return from POS',
                'created_by' => auth()->id() ?? 1,
            ]);

            $totalReturn = 0;
            $totalTax = 0;

            foreach ($data['items'] as $item) {
                $originalLine = SalesInvoiceLine::find($item['line_id']);
                if (!$originalLine || $originalLine->sales_invoice_id !== $originalInvoice->id)
                    continue;

                $quantity = (float) $item['quantity'];

                // VALIDATION: Prevent returning more than originally purchased minus already returned
                $alreadyReturned = \Modules\Sales\Models\SalesReturnLine::whereHas('salesReturn', function ($q) use ($originalInvoice) {
                    $q->where('sales_invoice_id', $originalInvoice->id);
                })->where('product_id', $originalLine->product_id)->sum('quantity');

                if (($alreadyReturned + $quantity) > (float) $originalLine->quantity) {
                    $avail = (float) $originalLine->quantity - $alreadyReturned;
                    throw new \RuntimeException("الكمية المرتجعة أكبر من المتاح. المتاح للمرتجع من ({$originalLine->product->name}) هو {$avail} فقط.");
                }

                $unitPrice = (float) $originalLine->unit_price;
                $lineTotal = $unitPrice * $quantity;
                $lineTax = $originalLine->tax_amount * ($quantity / $originalLine->quantity);

                // 2. Create Return Line
                \Modules\Sales\Models\SalesReturnLine::create([
                    'sales_return_id' => $return->id,
                    'product_id' => $originalLine->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal + $lineTax,
                    'notes' => 'POS Return',
                ]);

                $totalReturn += $lineTotal;
                $totalTax += $lineTax;

                // 3. Restore Stock with Lock
                $this->restoreStock($originalLine->product_id, $quantity, $return->return_number);
            }

            $return->update([
                'subtotal' => $totalReturn,
                'tax_amount' => $totalTax,
                'total_amount' => $totalReturn + $totalTax,
            ]);

            // 4. Accounting Reversal (Truth Rule #10)
            $this->createReturnJournalEntry($return);

            return $return;
        });
    }

    /**
     * Create Journal Entry for Sales Return
     */
    protected function createReturnJournalEntry(\Modules\Sales\Models\SalesReturn $return): void
    {
        $salesCode = Setting::getValue('acc_sales_revenue', '4101');
        $taxCode = Setting::getValue('acc_tax_payable', '2201');
        $cashCode = Setting::getValue('acc_cash', '1101');

        $salesAccount = Account::where('code', $salesCode)->first();
        $taxAccount = Account::where('code', $taxCode)->first();
        $cashAccount = Account::where('code', $cashCode)->first();

        $lines = [
            // DR: Revenue (Cancel out the sale)
            ['account_id' => $salesAccount->id, 'debit' => (float) $return->subtotal, 'credit' => 0],
            // DR: Tax (Cancel out the VAT)
            ['account_id' => $taxAccount->id, 'debit' => (float) $return->tax_amount, 'credit' => 0],
            // CR: Cash (Refunded to customer)
            ['account_id' => $cashAccount->id, 'debit' => 0, 'credit' => (float) $return->total_amount],
        ];

        $entry = $this->journalService->create([
            'entry_date' => now(),
            'reference' => $return->return_number,
            'description' => 'POS Return: ' . $return->return_number,
            'source_type' => \Modules\Sales\Models\SalesReturn::class,
            'source_id' => $return->id,
        ], $lines);

        $this->journalService->post($entry);
    }

    /**
     * Restore stock for returns
     */
    protected function restoreStock(int $productId, float $quantity, string $reference): void
    {
        $product = Product::findOrFail($productId);
        $stock = \Modules\Inventory\Models\ProductStock::where('product_id', $productId)->first();
        $warehouseId = $stock?->warehouse_id ?? Warehouse::first()?->id ?? 1;
        $warehouse = Warehouse::findOrFail($warehouseId);
        $unitCost = $stock?->average_cost ?? $product->cost_price;

        $this->inventoryService->addStock(
            $product,
            $warehouse,
            $quantity,
            $unitCost,
            MovementType::RETURN_IN,
            $reference,
            'Sales Return'
        );
    }

    /**
     * Create Journal Entry for POS Sale
     * Ensures VAT, Item Revenue, and Shipping Revenue are separated.
     * Enforces Accounting Truth for Discounts (4301) and Delivery Settlement (1202).
     */
    protected function createInvoiceJournalEntry(SalesInvoice $invoice, array $payments): void
    {
        $cashCode = Setting::getValue('acc_cash', '1101');
        $bankCode = Setting::getValue('acc_bank', '1102');
        $arCode = Setting::getValue('acc_ar', '1201');
        $salesCode = Setting::getValue('acc_sales_revenue', '4101');
        $taxCode = Setting::getValue('acc_tax_payable', '2201');
        $shippingCode = Setting::getValue('acc_shipping_revenue', '4201');
        $discountCode = '4301'; // Sales Discounts
        $pendingDeliveryCode = '1202'; // Pending Delivery Settlement

        $cashAccount = Account::where('code', $cashCode)->first();
        $bankAccount = Account::where('code', $bankCode)->first();
        $arAccount = $invoice->customer->account_id
            ? Account::find($invoice->customer->account_id)
            : Account::where('code', $arCode)->first();
        $salesAccount = Account::where('code', $salesCode)->first();
        $taxAccount = Account::where('code', $taxCode)->first();
        $shippingAccount = Account::where('code', $shippingCode)->first();
        $discountAccount = Account::where('code', $discountCode)->first();
        $pendingDeliveryAccount = Account::where('code', $pendingDeliveryCode)->first();

        $lines = [];

        // --- DEBITS (Payments & AR) ---

        foreach ($payments as $p) {
            $amount = (float) $p['amount'];
            if ($amount <= 0)
                continue;

            if ($p['method'] === 'credit') {
                // Already handled in balance_due logic below
                continue;
            }

            // Determine specific payment account
            $account = null;
            if (isset($p['account_id'])) {
                $account = Account::find($p['account_id']);
            } else {
                $account = ($p['method'] === 'card' || $p['method'] === 'bank') ? $bankAccount : $cashAccount;
            }

            if ($account) {
                $lines[] = ['account_id' => $account->id, 'debit' => $amount, 'credit' => 0, 'description' => 'POS ' . ucfirst($p['method']) . ' - ' . $invoice->invoice_number];
            }
        }

        // Handle Balance Due (AR)
        $deliveryFee = (float) $invoice->delivery_fee;
        $arAmount = (float) $invoice->balance_due;

        if ($invoice->is_delivery && $pendingDeliveryAccount) {
            // Priority: Delivery fee goes to Pending account
            $feeDebit = min($arAmount, $deliveryFee);
            if ($feeDebit > 0) {
                $lines[] = [
                    'account_id' => $pendingDeliveryAccount->id,
                    'debit' => $feeDebit,
                    'credit' => 0,
                    'description' => 'Pending Delivery Fee - ' . $invoice->invoice_number
                ];
                $arAmount -= $feeDebit;
            }
        }

        if ($arAmount > 0 && $arAccount) {
            $lines[] = [
                'account_id' => $arAccount->id,
                'debit' => $arAmount,
                'credit' => 0,
                'subledger_type' => Customer::class,
                'subledger_id' => $invoice->customer_id,
                'description' => 'POS AR - ' . $invoice->invoice_number
            ];
        }

        // Sales Discount (Debit)
        if ((float) $invoice->discount_amount > 0 && $discountAccount) {
            $lines[] = [
                'account_id' => $discountAccount->id,
                'debit' => (float) $invoice->discount_amount,
                'credit' => 0,
                'description' => 'Sales Discount - ' . $invoice->invoice_number
            ];
        }

        // --- CREDITS (Revenue & Tax) ---

        // Truth: Revenue and Tax must be derived from actual invoice lines
        $totalNetRevenue = $invoice->lines->sum('subtotal') ?: $invoice->subtotal;
        $totalTaxCredit = $invoice->lines->sum('tax_amount') ?: $invoice->tax_amount;

        // Sales Revenue (Net)
        $lines[] = [
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => round($totalNetRevenue, 2),
            'description' => 'Sales Revenue - ' . $invoice->invoice_number
        ];

        // Tax Payable (Credit)
        if ($totalTaxCredit > 0 && $taxAccount) {
            $lines[] = [
                'account_id' => $taxAccount->id,
                'debit' => 0,
                'credit' => round($totalTaxCredit, 2),
                'description' => 'VAT Output - ' . $invoice->invoice_number
            ];
        }

        // Shipping Revenue (separate credit)
        if ($deliveryFee > 0 && $shippingAccount) {
            $lines[] = [
                'account_id' => $shippingAccount->id,
                'debit' => 0,
                'credit' => round($deliveryFee, 2),
                'description' => 'Shipping Revenue'
            ];
        }

        // Final Verification: The JournalService->create will call validateBalance
        // If our logic doesn't result in Balance, it MUST fail here to protect the Ledger.
        $entry = $this->journalService->create([
            'entry_date' => now(),
            'reference' => $invoice->invoice_number,
            'description' => 'POS Split Sale: ' . $invoice->invoice_number,
            'source_type' => SalesInvoice::class,
            'source_id' => $invoice->id,
        ], $lines);

        $this->journalService->post($entry);
        $invoice->update(['journal_entry_id' => $entry->id]);
    }
}
