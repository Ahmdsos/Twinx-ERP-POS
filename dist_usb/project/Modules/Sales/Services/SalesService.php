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
use Modules\Sales\Models\SalesReturn;
use Modules\Sales\Models\SalesReturnLine;
use Modules\Sales\Enums\SalesReturnStatus;
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

            // CRITICAL: Refresh the invoice model to pick up totals
            // calculated by SalesInvoiceLine::boot() -> saved -> recalculateTotals().
            // Without this, $invoice has stale zero values from the initial create().
            $invoice->refresh();

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
        // Guard: Never create a zero-amount journal entry
        if ($invoice->total <= 0 && $invoice->subtotal <= 0) {
            throw new \RuntimeException(
                "Cannot create journal entry for invoice {$invoice->invoice_number}: " .
                "total={$invoice->total}, subtotal={$invoice->subtotal}. " .
                "Ensure invoice lines exist and totals are calculated before posting."
            );
        }

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
            // Base Revenue (Subtotal)
            ['account_id' => $salesAccount->id, 'debit' => 0, 'credit' => $invoice->subtotal],
        ];

        // Handle Tax
        if ($taxAccount && $invoice->tax_amount > 0) {
            $lines[] = ['account_id' => $taxAccount->id, 'debit' => 0, 'credit' => $invoice->tax_amount];
        }

        // Handle Delivery Fee (Shipping Revenue)
        if ($invoice->delivery_fee > 0) {
            $shippingCode = \App\Models\Setting::getValue('acc_shipping_revenue', '4103');
            $shippingAccount = Account::where('code', $shippingCode)->first() ?? $salesAccount;
            $lines[] = [
                'account_id' => $shippingAccount->id,
                'debit' => 0,
                'credit' => $invoice->delivery_fee,
                'description' => 'Delivery Fee'
            ];
        }

        // Handle Header Discount (Contra-Revenue -> Debit)
        if ($invoice->discount_amount > 0) {
            // Debit Sales Revenue (or Discount Expense)
            $lines[] = [
                'account_id' => $salesAccount->id,
                'debit' => $invoice->discount_amount,
                'credit' => 0,
                'description' => 'Invoice Discount'
            ];
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

    /**
     * Update existing Sales Invoice (Admin Full Control)
     */
    public function updateInvoice(SalesInvoice $invoice, array $data, array $lines): SalesInvoice
    {
        return DB::transaction(function () use ($invoice, $data, $lines) {
            // 1. Reverse existing AR/Revenue Journal Entry
            if ($invoice->journal_entry_id) {
                // Helper to reverse (create negative entry or delete if draft)
                // Assuming JournalService has a reverse method or we do manually
                // For simplicity/audit, we will VOID/Reverse the old one if posted, or delete if not.
                // Here we assume deleting/recreating for "Edit" context if system allows, 
                // OR creating a reversal entry. Let's try to delete the link and let the service handle it?
                // Better approach: Update the existing entry or Create Reversal + New.
                // SIMPLEST FOR MVP: Delete old entry lines and headers if soft-delete enabled,
                // but for strict accounting, we should create a reversal. 
                // Given the user wants "Edit", we'll treat it as a correction.

                $oldEntry = $invoice->journalEntry;
                if ($oldEntry) {
                    // CRITICAL FIX: Reverse impact on Account Balances before deleting!
                    // If we just delete, the Account->balance remains inflated (cached).
                    if ($oldEntry->status === \Modules\Accounting\Enums\JournalStatus::POSTED) {
                        // Manually subtract balances (multiplier -1) because we are "unposting" it physically
                        // We access the protected method via reflection or just duplicate logic?
                        // Better: Use JournalService if available. 
                        // Since updateAccountBalances is protected in JournalService, we can't call it directly.
                        // Ideally, we should add 'unpost' or 'deletePosted' to JournalService. 
                        // But for now, let's just create a reversal entry? No, user wants clean edit.
                        // TRICK: We will simply call a new public method `unpost` I'll add to JournalService, 
                        // or failing that, we just rely on `recalculate-balances` command for now? 
                        // NO, we must fix it.
                        // Let's add `deletePosted` to JournalService.
                        $this->journalService->deletePosted($oldEntry);
                    } else {
                        $oldEntry->lines()->delete();
                        $oldEntry->delete();
                    }
                }
            }

            // 2. Update Header
            $invoice->update([
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'],
                'notes' => $data['notes'] ?? $invoice->notes,
                'terms' => $data['terms'] ?? $invoice->terms,
            ]);

            // 3. Process Lines & Inventory (The Tricky Part)
            // If Invoice has DO, we usually don't touch inventory here because DO handles it.
            // BUT User requested ADMIN CONTROL to edit quantities.
            // If we edit Qty on DO-linked invoice, we effectively decouple it from the DO quantity visually,
            // or we must allow it.
            // DECISION: If DO exists, we update the invoice lines primarily for PRICING/Billing. 
            // Inventory impact was already done at DO level. 
            // IF Qty changes, it means we are billing for more/less than delivered? 
            // Or are we saying the DO was wrong? 
            // *Risk*: Discrepancy between Delivered vs Invoiced.
            // *Mitigation*: We will update Invoice Lines. If Direct Invoice, we adjust stock.

            $existingLines = $invoice->lines->keyBy('id');
            $newLineIds = [];

            foreach ($lines as $lineData) {
                $product = Product::find($lineData['product_id']);

                // Check if updating existing line or creating new
                $lineId = $lineData['id'] ?? null;
                $line = $invoice->lines()->updateOrCreate(
                    ['id' => $lineId],
                    [
                        'product_id' => $lineData['product_id'],
                        'quantity' => $lineData['quantity'],
                        'unit_price' => $lineData['unit_price'],
                        'discount_percent' => $lineData['discount_percent'] ?? 0,
                        'tax_percent' => $lineData['tax_percent'] ?? 0,
                        'description' => $lineData['description'] ?? $product->name,
                    ]
                );

                // DIRECT INVOICE STOCK ADJUSTMENT (No Delivery Order)
                if (!$invoice->delivery_order_id) {
                    $oldQty = $existingLines[$line->id]->quantity ?? 0;
                    $newQty = $lineData['quantity'];
                    $diff = $newQty - $oldQty;

                    if ($diff != 0) {
                        // If New > Old, we sold more -> Remove Stock
                        // If New < Old, we sold less -> Add Stock (Return)
                        $type = $diff > 0 ? MovementType::SALE : MovementType::RETURN_IN;
                        $absDiff = abs($diff);

                        $this->inventoryService->removeStock(
                            product: $product,
                            warehouse: $invoice->warehouse ?? Warehouse::first(), // Fallback
                            quantity: $absDiff,
                            type: $type,
                            reference: 'ADJ-' . $invoice->invoice_number,
                            notes: "Invoice Edit Adjustment",
                            sourceType: SalesInvoice::class,
                            sourceId: $invoice->id,
                            createJournal: true // Update COGS/Inventory
                        );
                    }
                }

                $newLineIds[] = $line->id;
            }

            // Delete removed lines
            try {
                $removedLines = $invoice->lines()->whereNotIn('id', $newLineIds)->get();
                foreach ($removedLines as $removedLine) {
                    if (!$invoice->delivery_order_id) {
                        // Restore stock for removed lines
                        $this->inventoryService->addStock(
                            product: $removedLine->product,
                            warehouse: $invoice->warehouse ?? Warehouse::first(),
                            quantity: $removedLine->quantity,
                            unitCost: $removedLine->product->cost_price ?? 0, // Fallback to current cost
                            type: MovementType::RETURN_IN,
                            reference: 'DEL-' . $invoice->invoice_number,
                            notes: "Invoice Line Deleted",
                            sourceType: SalesInvoice::class,
                            sourceId: $invoice->id
                        );
                    }
                    $removedLine->delete();
                }
            } catch (\Exception $e) { /* Ignore if already deleted */
            }

            $invoice->refresh();
            $invoice->recalculateTotals();

            // 4. Re-create Journal Entry
            $this->createInvoiceJournalEntry($invoice);

            // 5. Sync with POS Shift (CRITICAL for Report Consistency)
            if ($invoice->pos_shift_id) {
                $shift = \Modules\Sales\Models\PosShift::find($invoice->pos_shift_id);
                if ($shift) {
                    $realTotal = \Modules\Sales\Models\SalesInvoice::where('pos_shift_id', $shift->id)
                        ->whereIn('status', ['paid', 'partial', 'pending'])
                        ->sum('total');
                    $shift->update(['total_amount' => $realTotal]);
                }
            }

            return $invoice->fresh();
        });
    }

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
     * Failure Flow: System-Wide Reversal (Audit Finding #1 & #2)
     * Reverses Stock, COGS, Invoices, and AR while restoring SO quantities.
     */
    protected function handleDeliveryFailure(DeliveryOrder $delivery, array $params): void
    {
        $delivery->load(['lines.product', 'lines.salesOrderLine', 'salesOrder', 'salesInvoice', 'warehouse']);

        // 1. Create Sales Return Document (Audit Trail)
        $return = SalesReturn::create([
            'customer_id' => $delivery->customer_id,
            'sales_invoice_id' => $delivery->sales_invoice_id,
            'warehouse_id' => $delivery->warehouse_id,
            'return_date' => now(),
            'status' => SalesReturnStatus::APPROVED, // Auto-approved as it's a logistics failure
            'reason' => $params['notes'] ?? 'Failed Delivery Return',
            'created_by' => auth()->id(),
        ]);

        foreach ($delivery->lines as $line) {
            $product = $line->product;
            $qty = (float) $line->quantity;

            // 1a. Create Return Line
            $return->lines()->create([
                'product_id' => $product->id,
                'quantity' => $qty,
                'unit_price' => $line->salesOrderLine->unit_price ?? $product->selling_price,
                'tax_amount' => $line->salesOrderLine
                    ? ($line->salesOrderLine->tax_amount * ($qty / $line->salesOrderLine->quantity))
                    : 0,
                'line_total' => $line->salesOrderLine
                    ? ($line->salesOrderLine->line_total * ($qty / $line->salesOrderLine->quantity))
                    : ($qty * ($product->selling_price ?? 0)),
                'item_condition' => 'resalable',
            ]);

            // 1b. Restore Physical Stock (Type: RETURN_IN)
            // This creates Journal: DR Inventory / CR COGS (Reversing COGS)
            $this->inventoryService->addStock(
                product: $product,
                warehouse: $delivery->warehouse,
                quantity: $qty,
                unitCost: (float) $line->unit_cost,
                type: MovementType::RETURN_IN,
                reference: 'FAIL-' . $delivery->do_number,
                notes: 'Failed Delivery Return: ' . $delivery->do_number,
                sourceType: SalesReturn::class,
                sourceId: $return->id
            );

            // 1c. Restore Sales Order Line Quantities
            if ($line->salesOrderLine) {
                $line->salesOrderLine->decrement('delivered_quantity', $qty);
                // If the delivery was already invoiced, we must reverse that as well
                if ($line->salesOrderLine->invoiced_quantity >= $qty) {
                    $line->salesOrderLine->decrement('invoiced_quantity', $qty);
                }
            }
        }

        // 2. Financial Reversal (Invoice & AR)
        if ($delivery->salesInvoice) {
            $invoice = $delivery->salesInvoice;

            // Mark Invoice as Cancelled
            $invoice->update(['status' => SalesInvoiceStatus::CANCELLED]);

            // Reverse Invoice Journal Entry (AR / Revenue / Tax Payable)
            if ($invoice->journal_entry_id) {
                $entry = \Modules\Accounting\Models\JournalEntry::find($invoice->journal_entry_id);
                if ($entry && $entry->status === \Modules\Accounting\Enums\JournalStatus::POSTED) {
                    $this->journalService->reverse($entry, "Reversal due to Failed Delivery: {$delivery->do_number}");
                }
            }
        }

        // 3. Logistics Fee Cleanup (Pending -> Revenue Reversal)
        $this->reversePendingDeliveryFee($delivery);

        // 4. Final Updates
        if ($delivery->salesOrder) {
            $delivery->salesOrder->recalculateTotals();
            $delivery->salesOrder->updateDeliveryStatus();
        }
    }

    /**
     * Helper: Reverse Pending Delivery Fee
     */
    protected function reversePendingDeliveryFee(DeliveryOrder $delivery): void
    {
        $pendingAccount = Account::where('code', \App\Models\Setting::getValue('acc_pending_delivery', '1202'))->first();
        $shippingRevenueAccount = Account::where('code', \App\Models\Setting::getValue('acc_shipping_revenue', '4103'))->first();

        if (!$pendingAccount || !$shippingRevenueAccount) {
            return;
        }

        $deliveryFee = $delivery->salesOrder->delivery_fee ?? $delivery->salesInvoice->delivery_fee ?? 0;

        if ($deliveryFee <= 0) {
            return;
        }

        // DR Shipping Revenue / CR Pending Delivery
        $lines = [
            ['account_id' => $shippingRevenueAccount->id, 'debit' => (float) $deliveryFee, 'credit' => 0],
            ['account_id' => $pendingAccount->id, 'debit' => 0, 'credit' => (float) $deliveryFee],
        ];

        $entry = $this->journalService->create([
            'entry_date' => now(),
            'reference' => 'FAIL-' . $delivery->do_number,
            'description' => "Delivery Fee Reversal (Failure): " . $delivery->do_number,
            'source_type' => DeliveryOrder::class,
            'source_id' => $delivery->id,
        ], $lines);

        $this->journalService->post($entry);
    }
}
