<?php

namespace Modules\Sales\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Customer;
use Modules\Sales\Models\SalesOrder;
use Modules\Sales\Models\SalesOrderLine;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Models\DeliveryOrderLine;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Sales\Models\CustomerPayment;
use Modules\Sales\Models\Quotation;
use Modules\Sales\Models\QuotationLine;
use Modules\Sales\Enums\SalesOrderStatus;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Sales\Enums\SalesInvoiceStatus;
use Modules\Sales\Enums\QuotationStatus;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Models\Account;

/**
 * SalesService - Core service for sales operations
 */
class SalesService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected JournalService $journalService
    ) {
    }

    // ========================================
    // Sales Order Operations
    // ========================================

    /**
     * Create a new sales order
     */
    public function createSalesOrder(array $data, array $lines): SalesOrder
    {
        return DB::transaction(function () use ($data, $lines) {
            $customer = Customer::find($data['customer_id']);

            // Calculate order total for credit check
            $orderTotal = collect($lines)->sum(function ($line) {
                $quantity = $line['quantity'] ?? 0;
                $unitPrice = $line['unit_price'] ?? 0;
                $discount = $line['discount_percent'] ?? 0;
                $lineTotal = $quantity * $unitPrice * (1 - $discount / 100);
                return $lineTotal;
            });

            // Credit limit validation (P2 - Sprint 10)
            if (!$customer->canPlaceOrder($orderTotal)) {
                $reason = $customer->getOrderBlockReason($orderTotal);
                throw new \RuntimeException($reason ?? 'لا يمكن إنشاء الطلب - تجاوز حد الائتمان');
            }

            $so = SalesOrder::create([
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'] ?? now(),
                'expected_date' => $data['expected_date'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'status' => SalesOrderStatus::DRAFT,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? $customer->getShippingAddressFormatted(),
                'shipping_method' => $data['shipping_method'] ?? null,
                'currency' => $data['currency'] ?? 'EGP',
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $lineData) {
                $product = Product::find($lineData['product_id']);
                $so->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'] ?? $product->selling_price,
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                    'tax_percent' => $lineData['tax_percent'] ?? $product->tax_rate ?? 0,
                    'unit_id' => $lineData['unit_id'] ?? $product->unit_id,
                    'description' => $lineData['description'] ?? null,
                ]);
            }

            return $so->fresh(['lines', 'customer']);
        });
    }

    /**
     * Confirm a sales order
     */
    public function confirmSalesOrder(SalesOrder $so): bool
    {
        return $so->confirm();
    }

    // ========================================
    // Delivery Order Operations
    // ========================================

    /**
     * Create delivery order from sales order
     */
    public function createDelivery(
        SalesOrder $so,
        Warehouse $warehouse,
        array $itemsToDeliver,
        ?string $shippingMethod = null,
        ?string $notes = null
    ): DeliveryOrder {
        if (!$so->canDeliver()) {
            throw new \RuntimeException("Cannot deliver SO in status: {$so->status->label()}");
        }

        return DB::transaction(function () use ($so, $warehouse, $itemsToDeliver, $shippingMethod, $notes) {
            $do = DeliveryOrder::create([
                'sales_order_id' => $so->id,
                'customer_id' => $so->customer_id,
                'warehouse_id' => $warehouse->id,
                'delivery_date' => now(),
                'status' => DeliveryStatus::READY,
                'shipping_address' => $so->shipping_address,
                'shipping_method' => $shippingMethod ?? $so->shipping_method,
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            $totalCost = 0;

            foreach ($itemsToDeliver as $item) {
                $soLine = SalesOrderLine::find($item['sales_order_line_id']);
                if (!$soLine || $soLine->sales_order_id !== $so->id) {
                    continue;
                }

                $product = $soLine->product;
                $quantity = min($item['quantity'], $soLine->getRemainingToDeliver());

                // Remove stock via InventoryService (returns cost for COGS)
                $stockMovement = $this->inventoryService->removeStock(
                    $product,
                    $warehouse,
                    $quantity,
                    MovementType::SALE,
                    $do->do_number,
                    "Delivery for SO: {$so->so_number}",
                    DeliveryOrder::class,
                    $do->id
                );

                $unitCost = $stockMovement->unit_cost;

                $doLine = DeliveryOrderLine::create([
                    'delivery_order_id' => $do->id,
                    'sales_order_line_id' => $soLine->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'stock_movement_id' => $stockMovement->id,
                ]);

                $soLine->addDeliveredQuantity($quantity);
                $totalCost += $quantity * $unitCost;
            }

            // Complete delivery and create COGS journal
            $do->complete();
            $this->createCogsJournalEntry($do, $totalCost);

            return $do->fresh(['lines', 'salesOrder', 'warehouse']);
        });
    }

    /**
     * Create COGS journal entry for delivery
     */
    protected function createCogsJournalEntry(DeliveryOrder $do, float $totalCost): void
    {
        $cogsAccount = Account::where('code', '5101')->first(); // COGS
        $inventoryAccount = Account::where('code', '1301')->first(); // Inventory

        if (!$cogsAccount || !$inventoryAccount) {
            return;
        }

        $entry = $this->journalService->create([
            'entry_date' => $do->delivery_date,
            'reference' => $do->do_number,
            'description' => "COGS for Delivery to {$do->customer->name}",
            'source_type' => DeliveryOrder::class,
            'source_id' => $do->id,
        ], [
            ['account_id' => $cogsAccount->id, 'debit' => $totalCost, 'credit' => 0],
            ['account_id' => $inventoryAccount->id, 'debit' => 0, 'credit' => $totalCost],
        ]);

        $do->update(['journal_entry_id' => $entry->id]);
    }

    // ========================================
    // Sales Invoice Operations
    // ========================================

    /**
     * Create sales invoice from delivery order
     */
    public function createInvoiceFromDelivery(
        DeliveryOrder $do,
        ?Carbon $invoiceDate = null,
        ?Carbon $dueDate = null
    ): SalesInvoice {
        return DB::transaction(function () use ($do, $invoiceDate, $dueDate) {
            $so = $do->salesOrder;
            $customer = $do->customer;
            $paymentTerms = $customer->payment_terms ?? 30;
            $invoiceDate = $invoiceDate ?? now();
            $dueDate = $dueDate ?? $invoiceDate->copy()->addDays($paymentTerms);

            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'sales_order_id' => $so->id,
                'delivery_order_id' => $do->id,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => SalesInvoiceStatus::PENDING,
                'created_by' => auth()->id(),
            ]);

            // Create lines from SO lines that were delivered
            foreach ($do->lines as $doLine) {
                $soLine = $doLine->salesOrderLine;
                if (!$soLine)
                    continue;

                $invoice->lines()->create([
                    'product_id' => $doLine->product_id,
                    'description' => $soLine->product->name,
                    'quantity' => $doLine->quantity,
                    'unit_price' => $soLine->unit_price,
                    'discount_percent' => $soLine->discount_percent,
                    'tax_percent' => $soLine->tax_percent,
                ]);

                $soLine->addInvoicedQuantity($doLine->quantity);
            }

            // Create AR Journal Entry
            $this->createInvoiceJournalEntry($invoice);

            return $invoice->fresh(['lines', 'customer']);
        });
    }

    /**
     * Create journal entry for sales invoice (AR & Revenue)
     */
    protected function createInvoiceJournalEntry(SalesInvoice $invoice): void
    {
        $arAccount = Account::where('code', '1201')->first(); // AR
        $salesAccount = Account::where('code', '4101')->first(); // Sales Revenue
        $taxAccount = Account::where('code', '2105')->first(); // Tax Payable

        if (!$arAccount || !$salesAccount) {
            return;
        }

        $lines = [
            ['account_id' => $arAccount->id, 'debit' => $invoice->total, 'credit' => 0],
            ['account_id' => $salesAccount->id, 'debit' => 0, 'credit' => $invoice->subtotal],
        ];

        if ($taxAccount && $invoice->tax_amount > 0) {
            $lines[] = ['account_id' => $taxAccount->id, 'debit' => 0, 'credit' => $invoice->tax_amount];
        }

        $entry = $this->journalService->create([
            'entry_date' => $invoice->invoice_date,
            'reference' => $invoice->invoice_number,
            'description' => "Sales Invoice to {$invoice->customer->name}",
            'source_type' => SalesInvoice::class,
            'source_id' => $invoice->id,
        ], $lines);

        $invoice->update(['journal_entry_id' => $entry->id]);
    }

    // ========================================
    // Customer Payment Operations
    // ========================================

    /**
     * Receive payment from customer
     */
    public function receivePayment(
        Customer $customer,
        float $amount,
        Account $paymentAccount,
        ?string $paymentMethod = 'cash',
        ?string $reference = null,
        ?Carbon $paymentDate = null,
        array $invoiceAllocations = []
    ): CustomerPayment {
        return DB::transaction(function () use ($customer, $amount, $paymentAccount, $paymentMethod, $reference, $paymentDate, $invoiceAllocations) {
            $payment = CustomerPayment::create([
                'customer_id' => $customer->id,
                'payment_date' => $paymentDate ?? now(),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_account_id' => $paymentAccount->id,
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);

            foreach ($invoiceAllocations as $allocation) {
                // Ensure allocation is an array (could be string or object)
                if (is_string($allocation)) {
                    $allocation = json_decode($allocation, true) ?? [];
                }
                if (!is_array($allocation) || empty($allocation['invoice_id'])) {
                    continue;
                }

                $invoice = SalesInvoice::find($allocation['invoice_id']);
                if ($invoice && $invoice->customer_id === $customer->id) {
                    $allocAmount = min($allocation['amount'] ?? 0, $invoice->balance_due);
                    if ($allocAmount > 0) {
                        $payment->allocateToInvoice($invoice, $allocAmount);
                    }
                }
            }


            // Create journal entry: DR Cash/Bank, CR AR
            $this->createPaymentJournalEntry($payment, $customer);

            return $payment->fresh(['customer', 'allocations']);
        });
    }

    /**
     * Create journal entry for customer payment
     */
    protected function createPaymentJournalEntry(CustomerPayment $payment, Customer $customer): void
    {
        $arAccount = $customer->account_id
            ? Account::find($customer->account_id)
            : Account::where('code', '1201')->first();

        if (!$arAccount) {
            return;
        }

        $entry = $this->journalService->create([
            'entry_date' => $payment->payment_date,
            'reference' => $payment->receipt_number,
            'description' => "Payment from {$customer->name}",
            'source_type' => CustomerPayment::class,
            'source_id' => $payment->id,
        ], [
            ['account_id' => $payment->payment_account_id, 'debit' => $payment->amount, 'credit' => 0],
            ['account_id' => $arAccount->id, 'debit' => 0, 'credit' => $payment->amount],
        ]);

        $payment->update(['journal_entry_id' => $entry->id]);
    }

    // ========================================
    // Quotation Operations
    // ========================================

    /**
     * Create a new quotation
     */
    public function createQuotation(array $data, array $lines): Quotation
    {
        return DB::transaction(function () use ($data, $lines) {
            $customer = Customer::find($data['customer_id']);

            $quotation = Quotation::create([
                'customer_id' => $data['customer_id'],
                'quotation_date' => $data['quotation_date'] ?? now(),
                'valid_until' => $data['valid_until'] ?? now()->addDays(30),
                'status' => QuotationStatus::DRAFT,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'currency' => $data['currency'] ?? 'EGP',
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $lineData) {
                $product = Product::find($lineData['product_id']);
                $quotation->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'] ?? $product->sale_price,
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                    'tax_percent' => $lineData['tax_percent'] ?? $product->tax_rate ?? 0,
                    'unit_id' => $lineData['unit_id'] ?? $product->unit_id,
                    'description' => $lineData['description'] ?? $product->name,
                ]);
            }

            // Calculate totals
            $quotation->calculateTotals();

            return $quotation->fresh(['lines', 'customer']);
        });
    }

    /**
     * Convert accepted quotation to sales order
     */
    public function convertQuotationToOrder(Quotation $quotation): SalesOrder
    {
        if (!$quotation->canConvert()) {
            throw new \RuntimeException("Cannot convert quotation in status: {$quotation->status->label()}");
        }

        return DB::transaction(function () use ($quotation) {
            // Prepare lines data from quotation
            $lines = $quotation->lines->map(fn($line) => [
                'product_id' => $line->product_id,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'discount_percent' => $line->discount_percent,
                'tax_percent' => $line->tax_percent,
                'unit_id' => $line->unit_id,
                'description' => $line->description,
            ])->toArray();

            // Create sales order using existing method
            $so = $this->createSalesOrder([
                'customer_id' => $quotation->customer_id,
                'order_date' => now(),
                'notes' => $quotation->notes,
                'quotation_id' => $quotation->id,
                'currency' => $quotation->currency,
                'exchange_rate' => $quotation->exchange_rate,
            ], $lines);

            // Mark quotation as converted
            $quotation->markAsConverted();

            return $so;
        });
    }
}
