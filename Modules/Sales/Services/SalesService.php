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

                if ($discount > 100) {
                    throw new \RuntimeException("نسبة الخصم لا يمكن أن تتجاوز 100%");
                }

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
                'quotation_id' => $data['quotation_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $lineData) {
                $product = Product::find($lineData['product_id']);
                $so->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'] ?? $product->selling_price,
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                    'tax_percent' => $lineData['tax_percent'] ?? ($product->tax_rate > 0 ? $product->tax_rate : \App\Models\Setting::getValue('default_tax_rate', 0)),
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

    /**
     * Update an existing sales order
     */
    public function updateSalesOrder(SalesOrder $so, array $data, array $lines): SalesOrder
    {
        if (!$so->canEdit()) {
            throw new \RuntimeException("Cannot edit sales order in status: {$so->status->label()}");
        }

        return DB::transaction(function () use ($so, $data, $lines) {
            // Update header
            $so->update([
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_method' => $data['shipping_method'] ?? null,
            ]);

            // Sync lines (Atomic update)
            $existingLines = $so->lines->keyBy('id');
            $newLineIds = [];

            foreach ($lines as $lineData) {
                $product = Product::find($lineData['product_id']);

                // Logic check: If line has an ID, update it; otherwise, create it.
                // In this UI implementation, we usually send all lines.
                // To be truly atomic and safe, we match by product_id or a unique property if possible,
                // but usually, a 'sync' involves checking which lines are new.

                $line = $so->lines()->updateOrCreate(
                    ['product_id' => $lineData['product_id']],
                    [
                        'quantity' => $lineData['quantity'],
                        'unit_price' => $lineData['unit_price'] ?? $product->selling_price,
                        'discount_percent' => $lineData['discount_percent'] ?? 0,
                        'tax_percent' => $lineData['tax_percent'] ?? ($product->tax_rate > 0 ? $product->tax_rate : \App\Models\Setting::getValue('default_tax_rate', 0)),
                        'unit_id' => $lineData['unit_id'] ?? $product->unit_id,
                        'description' => $lineData['description'] ?? null,
                        'notes' => $lineData['notes'] ?? null,
                    ]
                );

                $newLineIds[] = $line->id;
            }

            // Remove lines that were not in the update request
            $so->lines()->whereNotIn('id', $newLineIds)->delete();

            // Refresh and recalculate
            $so->refresh();
            $so->recalculateTotals();

            return $so->fresh(['lines', 'customer']);
        });
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
                // Pass false for createJournal to prevent double-booking
                $stockMovement = $this->inventoryService->removeStock(
                    $product,
                    $warehouse,
                    $quantity,
                    MovementType::SALE,
                    $do->do_number,
                    "Delivery for SO: {$so->so_number}",
                    DeliveryOrder::class,
                    $do->id,
                    false
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

            // H-08: Complete delivery and trigger accounting
            // Truth: If it's a direct delivery (like POS), complete it immediately
            $this->completeDelivery($do);

            return $do->fresh(['lines', 'salesOrder', 'warehouse']);
        });
    }

    /**
     * Ship delivery order (H-13 Integration)
     */
    public function shipDelivery(DeliveryOrder $do, array $data): bool
    {
        return DB::transaction(function () use ($do, $data) {
            if (!$do->ship()) {
                return false;
            }

            $do->update([
                'driver_id' => $data['driver_id'] ?? $do->driver_id,
                'driver_name' => $data['driver_name'] ?? $do->driver_name,
                'vehicle_number' => $data['vehicle_number'] ?? $do->vehicle_number,
                'tracking_number' => $data['tracking_number'] ?? $do->tracking_number,
            ]);

            // Mark driver as busy/on_delivery
            if ($do->driver) {
                $do->driver->update(['status' => 'on_delivery']);
            }

            return true;
        });
    }

    /**
     * Complete delivery order (H-08: Accounting Trigger)
     */
    public function completeDelivery(DeliveryOrder $do): bool
    {
        return DB::transaction(function () use ($do) {
            if (!$do->complete()) {
                return false;
            }

            // Trigger COGS Journal Entry
            $this->triggerCogsEntry($do);

            // Mark driver as available
            if ($do->driver) {
                $do->driver->update(['status' => 'available']);
            }

            // Update Sales Order delivery status
            $do->salesOrder->updateDeliveryStatus();

            return true;
        });
    }

    /**
     * Cancel delivery order (H-13: Status Reversal)
     */
    public function cancelDelivery(DeliveryOrder $do): bool
    {
        return DB::transaction(function () use ($do) {
            if ($do->status === DeliveryStatus::DELIVERED) {
                throw new \RuntimeException("Cannot cancel a delivered order");
            }

            $do->update(['status' => DeliveryStatus::CANCELLED]);

            // Reverse driver status if they were on delivery
            if ($do->driver && $do->driver->status === 'on_delivery') {
                $do->driver->update(['status' => 'available']);
            }

            // Reverse SO quantities potentially? 
            // For now, following task requirements for driver status.

            return true;
        });
    }

    /**
     * Trigger COGS Journal Entry from existing DO (Truth Consolidation)
     */
    public function triggerCogsEntry(DeliveryOrder $do): void
    {
        if ($do->journal_entry_id) {
            return;
        }

        $totalCost = $do->getTotalCost();
        if ($totalCost <= 0) {
            return;
        }

        $this->createCogsJournalEntry($do, $totalCost);
    }

    /**
     * Create COGS journal entry for delivery
     */
    protected function createCogsJournalEntry(DeliveryOrder $do, float $totalCost): void
    {
        $cogsCode = \App\Models\Setting::getValue('acc_cogs', '5101');
        $inventoryCode = \App\Models\Setting::getValue('acc_inventory', '1301');

        $cogsAccount = Account::where('code', $cogsCode)->first(); // COGS
        $inventoryAccount = Account::where('code', $inventoryCode)->first(); // Inventory

        if (!$cogsAccount || !$inventoryAccount) {
            // CRITICAL: Do NOT silently fail - this hides configuration errors and causes financial data loss
            throw new \RuntimeException(
                "COGS Journal Entry Failed: Missing required accounts. " .
                "COGS Account (code: {$cogsCode}): " . ($cogsAccount ? 'Found' : 'NOT FOUND') . ", " .
                "Inventory Account (code: {$inventoryCode}): " . ($inventoryAccount ? 'Found' : 'NOT FOUND') . ". " .
                "Please configure these accounts in Settings or ensure they exist in Chart of Accounts."
            );
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

        // AUTO-POST to update account balances
        $this->journalService->post($entry);

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
            $customer = $do->customer ?? Customer::withTrashed()->find($do->customer_id);

            if (!$customer) {
                throw new \RuntimeException("Cannot create invoice: Customer not found for Delivery Order #{$do->do_number}");
            }

            $paymentTerms = $customer->payment_terms ?? 30;
            $invoiceDate = $invoiceDate ?? now();
            $dueDate = $dueDate ?? $invoiceDate->copy()->addDays($paymentTerms);

            $invoice = SalesInvoice::create([
                'customer_id' => $customer->id,
                'sales_order_id' => $so?->id, // Handle null SO
                'delivery_order_id' => $do->id,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => SalesInvoiceStatus::PENDING,
                'created_by' => auth()->id(),
            ]);

            // Create lines
            foreach ($do->lines as $doLine) {
                $soLine = $doLine->salesOrderLine;
                $product = $doLine->product;

                // Fallback logic if SO Line is missing
                $unitPrice = $soLine ? $soLine->unit_price : ($product->selling_price ?? 0);
                $discountPercent = $soLine ? $soLine->discount_percent : 0;
                $taxPercent = $soLine ? $soLine->tax_percent : ($product->tax_rate ?? 0);
                $description = $soLine ? $soLine->description : $product->name;

                $invoice->lines()->create([
                    'product_id' => $doLine->product_id,
                    'description' => $description,
                    'quantity' => $doLine->quantity,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_percent' => $taxPercent,
                ]);

                if ($soLine) {
                    $soLine->addInvoicedQuantity($doLine->quantity);
                }
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
        $arCode = \App\Models\Setting::getValue('acc_ar', '1201');
        $salesCode = \App\Models\Setting::getValue('acc_sales_revenue', '4101');
        $taxCode = \App\Models\Setting::getValue('acc_tax_payable', '2201');

        $arAccount = Account::where('code', $arCode)->first(); // AR
        $salesAccount = Account::where('code', $salesCode)->first(); // Sales Revenue
        $taxAccount = Account::where('code', $taxCode)->first(); // Tax Payable

        if (!$arAccount || !$salesAccount) {
            // CRITICAL: Do NOT silently fail - this hides configuration errors and causes financial data loss
            throw new \RuntimeException(
                "Invoice Journal Entry Failed: Missing required accounts. " .
                "AR Account (code: {$arCode}): " . ($arAccount ? 'Found' : 'NOT FOUND') . ", " .
                "Sales Revenue Account (code: {$salesCode}): " . ($salesAccount ? 'Found' : 'NOT FOUND') . ". " .
                "Please configure these accounts in Settings or ensure they exist in Chart of Accounts."
            );
        }

        $lines = [
            [
                'account_id' => $arAccount->id,
                'debit' => $invoice->total,
                'credit' => 0,
                'subledger_type' => \Modules\Sales\Models\Customer::class,
                'subledger_id' => $invoice->customer_id
            ],
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

        // AUTO-POST to update account balances
        $this->journalService->post($entry);

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

            // Process invoice allocations (ensure it's an array)
            $allocations = is_array($invoiceAllocations) ? $invoiceAllocations : [];

            foreach ($allocations as $allocation) {
                // Ensure allocation is an array with required keys
                if (!is_array($allocation) || !isset($allocation['invoice_id']) || !isset($allocation['amount'])) {
                    continue;
                }

                $invoice = SalesInvoice::find($allocation['invoice_id']);
                if ($invoice && $invoice->customer_id === $customer->id) {
                    $allocAmount = min($allocation['amount'], $invoice->balance_due);
                    $payment->allocateToInvoice($invoice, $allocAmount);
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
        $arCode = \App\Models\Setting::getValue('acc_ar', '1201');
        $arAccount = $customer->account_id
            ? Account::find($customer->account_id)
            : Account::where('code', $arCode)->first();

        if (!$arAccount) {
            return;
        }

        $entry = $this->journalService->create([
            'entry_date' => $payment->payment_date,
            'reference' => $payment->payment_reference,
            'description' => "Payment from {$customer->name}",
            'source_type' => CustomerPayment::class,
            'source_id' => $payment->id,
        ], [
            ['account_id' => $payment->payment_account_id, 'debit' => $payment->amount, 'credit' => 0],
            [
                'account_id' => $arAccount->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'subledger_type' => \Modules\Sales\Models\Customer::class,
                'subledger_id' => $customer->id
            ],
        ]);

        // AUTO-POST to update account balances
        $this->journalService->post($entry);

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
            // Multi-customer and Type-based logic
            $rawCustomerId = $data['customer_id'] ?? [];
            $customerIds = is_array($rawCustomerId) ? $rawCustomerId : (empty($rawCustomerId) ? [] : [$rawCustomerId]);
            $primaryCustomerId = !empty($customerIds) ? $customerIds[0] : null;

            $quotation = Quotation::create([
                'customer_id' => $primaryCustomerId,
                'target_customer_type' => $data['target_customer_type'] ?? null,
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

            // Sync multiple customers
            $quotation->customers()->sync($customerIds);

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

        if (!$quotation->customer_id) {
            throw new \RuntimeException("لا يمكن تحويل هذا العرض لأمر بيع مباشرة لأنه موجه لفئة كاملة من العملاء. يرجى إنشاء أمر بيع يدوي واختيار العميل.");
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
    /**
     * Settle Delivery Mission (Audit Finding #1 & #2)
     * Handles the transition of human-led delivery to final Truth state.
     */
    public function settleDeliveryMission(DeliveryOrder $delivery, string $status, array $params = []): void
    {
        DB::transaction(function () use ($delivery, $status, $params) {
            $newStatus = DeliveryStatus::from($status);

            if ($newStatus === DeliveryStatus::DELIVERED) {
                $this->handleDeliverySuccess($delivery, $params);
            } elseif ($newStatus === DeliveryStatus::RETURNED) {
                $this->handleDeliveryFailure($delivery, $params);
            }

            $delivery->update([
                'status' => $newStatus,
                'notes' => ($delivery->notes ? $delivery->notes . "\n" : "") . ($params['notes'] ?? ""),
                'delivered_at' => ($newStatus === DeliveryStatus::DELIVERED) ? now() : null,
            ]);

            if ($delivery->salesOrder) {
                $delivery->salesOrder->updateDeliveryStatus();
            }
        });
    }

    /**
     * Success Flow: Transfer Pending -> Cash/Bank
     */
    protected function handleDeliverySuccess(DeliveryOrder $delivery, array $params): void
    {
        $pendingAccount = Account::where('code', \App\Models\Setting::getValue('acc_pending_delivery', '1202'))->first();
        $cashAccount = Account::where('code', \App\Models\Setting::getValue('acc_cash', '1101'))->first();

        if (!$pendingAccount || !$cashAccount)
            return;

        $deliveryFee = $delivery->salesOrder->delivery_fee ?? $delivery->salesInvoice->delivery_fee ?? 0;

        if ($deliveryFee <= 0)
            return;

        // Transfer delivery fee from Pending to Cash
        $lines = [
            ['account_id' => $pendingAccount->id, 'debit' => 0, 'credit' => (float) $deliveryFee],
            ['account_id' => $cashAccount->id, 'debit' => (float) $deliveryFee, 'credit' => 0],
        ];

        $entry = $this->journalService->create([
            'entry_date' => now(),
            'reference' => 'SETTLE-' . $delivery->do_number,
            'description' => "Delivery Settlement (Success): " . $delivery->do_number,
            'source_type' => DeliveryOrder::class,
            'source_id' => $delivery->id,
        ], $lines);

        $this->journalService->post($entry);
    }

    /**
     * Failure Flow: Restore Stock & Reverse Revenue
     */
    protected function handleDeliveryFailure(DeliveryOrder $delivery, array $params): void
    {
        $delivery->load('lines.product');

        // 1. Restore Stock (Safe Stock Reversal - Audit Finding #2)
        foreach ($delivery->lines as $line) {
            $unitCost = $line->product->average_cost ?? $line->product->purchase_price ?? 0;
            $this->inventoryService->addStock(
                $line->product,
                $delivery->warehouse,
                $line->quantity,
                (float) $unitCost,
                MovementType::ADJUSTMENT_IN,
                'FAIL-' . $delivery->do_number,
                'Failed Delivery Return'
            );
        }

        // 2. Accounting Truth: Reverse Pending Fee (Audit Finding #1)
        $pendingAccount = Account::where('code', \App\Models\Setting::getValue('acc_pending_delivery', '1202'))->first();
        $shippingRevenueAccount = Account::where('code', \App\Models\Setting::getValue('acc_shipping_revenue', '4201'))->first();

        if (!$pendingAccount || !$shippingRevenueAccount)
            return;

        $deliveryFee = $delivery->salesOrder->delivery_fee ?? $delivery->salesInvoice->delivery_fee ?? 0;

        if ($deliveryFee <= 0)
            return;

        $lines = [
            ['account_id' => $pendingAccount->id, 'debit' => 0, 'credit' => (float) $deliveryFee],
            ['account_id' => $shippingRevenueAccount->id, 'debit' => (float) $deliveryFee, 'credit' => 0],
        ];

        $entry = $this->journalService->create([
            'entry_date' => now(),
            'reference' => 'FAIL-' . $delivery->do_number,
            'description' => "Delivery Settlement (Failure): " . $delivery->do_number,
            'source_type' => DeliveryOrder::class,
            'source_id' => $delivery->id,
        ], $lines);

        $this->journalService->post($entry);
    }
}
