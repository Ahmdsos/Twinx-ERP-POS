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

            // MATH TRUTH: Total = Net Subtotal + Total Tax - Global Discount + Delivery Fee
            $total = $subtotal + $totalTax - $globalDiscount + $deliveryFee;

            $amountPaid = (float) $data['amount_paid'];
            $balanceDue = max(0, $total - $amountPaid);

            // 2. Resolve Customer
            $customerId = $data['customer_id'] ?? $this->resolveWalkInCustomer()->id;

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
                'pos_shift_id' => $activeShift?->id,
            ]);

            // Track stats in shift
            if ($activeShift) {
                $activeShift->incrementSales($invoice->paid_amount, $data['payment_method'] ?? 'cash');
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

                // Stock Reduction (Sale vs Return)
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
                } else {
                    // Handle return stock restoration via InventoryService if needed
                }
            }

            // 5. Handle Delivery Integration
            if ($invoice->is_delivery) {
                $this->createDeliveryOrder($invoice, $data);
            }

            // 6. Record Payment
            if ($amountPaid > 0) {
                $this->recordPayment($invoice, $data);
            }

            // 7. Create Journal Entry
            $this->createInvoiceJournalEntry($invoice, $data['payment_method']);

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

        $lastInvoice = SalesInvoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $sequence = $lastInvoice ? (int) substr($lastInvoice->invoice_number, -4) + 1 : 1;
        $number = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        while (SalesInvoice::where('invoice_number', $number)->exists()) {
            $sequence++;
            $number = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }

        return $number;
    }

    /**
     * Create Delivery Order from Invoice
     */
    protected function createDeliveryOrder(SalesInvoice $invoice, array $data): void
    {
        $do = DeliveryOrder::create([
            'sales_invoice_id' => $invoice->id,
            'sales_order_id' => null, // Explicitly null for POS direct sales
            'customer_id' => $invoice->customer_id,
            'warehouse_id' => $invoice->warehouse_id,
            'delivery_date' => now(),
            'status' => DeliveryStatus::READY,
            'shipping_address' => $invoice->shipping_address ?? $invoice->customer->address,
            'driver_id' => $invoice->driver_id,
            'notes' => 'Delivery for POS invoice: ' . $invoice->invoice_number,
        ]);

        foreach ($invoice->lines as $line) {
            DeliveryOrderLine::create([
                'delivery_order_id' => $do->id,
                'product_id' => $line->product_id,
                'quantity' => $line->quantity,
                'unit_cost' => $line->product->cost_price ?? 0,
            ]);
        }

        if ($invoice->driver_id) {
            $driver = DeliveryDriver::find($invoice->driver_id);
            if ($driver) {
                $driver->update([
                    'status' => 'on_delivery',
                    'total_deliveries' => $driver->total_deliveries + 1
                ]);
            }
        }

        $invoice->update(['delivery_order_id' => $do->id]);
    }

    /**
     * Record Cash/Card Payment
     */
    protected function recordPayment(SalesInvoice $invoice, array $data): void
    {
        $paymentAccountId = $data['payment_account_id'] ?? Setting::getValue('acc_cash_id', 1);

        $payment = CustomerPayment::create([
            'customer_id' => $invoice->customer_id,
            'payment_account_id' => $paymentAccountId,
            'payment_date' => now(),
            'amount' => $invoice->paid_amount,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference' => 'POS-' . $invoice->invoice_number,
            'receipt_number' => 'RCT-' . now()->format('YmdHis'),
            'notes' => 'POS Payment for ' . $invoice->invoice_number,
            'created_by' => auth()->id(),
        ]);

        CustomerPaymentAllocation::create([
            'customer_payment_id' => $payment->id,
            'sales_invoice_id' => $invoice->id,
            'amount' => min($invoice->paid_amount, $invoice->total),
        ]);
    }

    /**
     * Handle POS Sales Return
     */
    public function salesReturn(array $data): SalesInvoice
    {
        return DB::transaction(function () use ($data) {
            $originalInvoice = SalesInvoice::findOrFail($data['invoice_id']);
            $returnNumber = 'RET-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $returnTotal = 0;
            $returnLines = [];

            foreach ($data['items'] as $item) {
                $originalLine = SalesInvoiceLine::find($item['line_id']);
                if (!$originalLine)
                    continue;

                $lineTotal = $originalLine->unit_price * $item['quantity'];
                $returnTotal += $lineTotal;

                $returnLines[] = [
                    'product_id' => $originalLine->product_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $originalLine->unit_price,
                    'line_total' => $lineTotal,
                ];

                $this->restoreStock($originalLine->product_id, (float) $item['quantity'], $returnNumber);
            }

            $returnInvoice = SalesInvoice::create([
                'invoice_number' => $returnNumber,
                'customer_id' => $originalInvoice->customer_id,
                'invoice_date' => now(),
                'due_date' => now(),
                'subtotal' => -$returnTotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => -$returnTotal,
                'paid_amount' => -$returnTotal,
                'balance_due' => 0,
                'status' => 'refunded',
                'notes' => 'مرتجع من فاتورة: ' . $originalInvoice->invoice_number . ($data['reason'] ? ' - السبب: ' . $data['reason'] : ''),
            ]);

            foreach ($returnLines as $line) {
                SalesInvoiceLine::create([
                    'sales_invoice_id' => $returnInvoice->id,
                    'product_id' => $line['product_id'],
                    'quantity' => -$line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => 0,
                    'tax_percent' => 0,
                    'tax_amount' => 0,
                    'line_total' => -$line['line_total'],
                ]);
            }

            return $returnInvoice;
        });
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
     */
    protected function createInvoiceJournalEntry(SalesInvoice $invoice, string $paymentMethod): void
    {
        $cashCode = Setting::getValue('acc_cash', '1101');
        $bankCode = Setting::getValue('acc_bank', '1102');
        $arCode = Setting::getValue('acc_ar', '1201');
        $salesCode = Setting::getValue('acc_sales_revenue', '4101');
        $taxCode = Setting::getValue('acc_tax_payable', '2201');
        $shippingCode = Setting::getValue('acc_shipping_revenue', '4201');

        $cashAccount = Account::where('code', $cashCode)->first();
        $bankAccount = Account::where('code', $bankCode)->first();
        $arAccount = $invoice->customer->account_id
            ? Account::find($invoice->customer->account_id)
            : Account::where('code', $arCode)->first();
        $salesAccount = Account::where('code', $salesCode)->first();
        $taxAccount = Account::where('code', $taxCode)->first();
        $shippingAccount = Account::where('code', $shippingCode)->first();

        // Determine payment account
        $paymentAccount = ($paymentMethod === 'card' || $paymentMethod === 'bank') ? ($bankAccount ?? $cashAccount) : $cashAccount;

        $lines = [];

        // DEBITS
        if ((float) $invoice->paid_amount > 0) {
            $lines[] = ['account_id' => $paymentAccount->id, 'debit' => (float) $invoice->paid_amount, 'credit' => 0];
        }
        if ((float) $invoice->balance_due > 0 && $arAccount) {
            $lines[] = [
                'account_id' => $arAccount->id,
                'debit' => (float) $invoice->balance_due,
                'credit' => 0,
                'subledger_type' => Customer::class,
                'subledger_id' => $invoice->customer_id
            ];
        }

        // CREDITS
        // Revenue = Total - VAT - Shipping
        $lines[] = [
            'account_id' => $salesAccount->id,
            'debit' => 0,
            'credit' => (float) $invoice->total - (float) $invoice->tax_amount - (float) $invoice->delivery_fee
        ];

        if ((float) $invoice->tax_amount > 0 && $taxAccount) {
            $lines[] = ['account_id' => $taxAccount->id, 'debit' => 0, 'credit' => (float) $invoice->tax_amount];
        }

        if ((float) $invoice->delivery_fee > 0 && $shippingAccount) {
            $lines[] = ['account_id' => $shippingAccount->id, 'debit' => 0, 'credit' => (float) $invoice->delivery_fee];
        }

        $entry = $this->journalService->create([
            'entry_date' => now(),
            'reference' => $invoice->invoice_number,
            'description' => 'POS Sale: ' . $invoice->invoice_number,
            'source_type' => SalesInvoice::class,
            'source_id' => $invoice->id,
        ], $lines);

        $this->journalService->post($entry);
        $invoice->update(['journal_entry_id' => $entry->id]);
    }
}
