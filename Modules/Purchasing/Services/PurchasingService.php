<?php

namespace Modules\Purchasing\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Models\PurchaseOrderLine;
use Modules\Purchasing\Models\Grn;
use Modules\Purchasing\Models\GrnLine;
use Modules\Purchasing\Models\PurchaseInvoice;
use Modules\Purchasing\Models\PurchaseInvoiceLine;
use Modules\Purchasing\Models\SupplierPayment;
use Modules\Purchasing\Enums\PurchaseOrderStatus;
use Modules\Purchasing\Enums\GrnStatus;
use Modules\Purchasing\Enums\PurchaseInvoiceStatus;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Enums\MovementType;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Accounting\Services\JournalService;
use Modules\Accounting\Models\Account;

/**
 * PurchasingService - Core service for purchasing operations
 */
class PurchasingService
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected JournalService $journalService
    ) {
    }

    // ========================================
    // Purchase Order Operations
    // ========================================

    /**
     * Create a new purchase order
     */
    public function createPurchaseOrder(array $data, array $lines): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $lines) {
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'] ?? now(),
                'expected_date' => $data['expected_date'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'status' => PurchaseOrderStatus::DRAFT,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'currency' => $data['currency'] ?? 'EGP',
                'exchange_rate' => $data['exchange_rate'] ?? 1,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $lineData) {
                $product = Product::find($lineData['product_id']);
                $po->lines()->create([
                    'product_id' => $lineData['product_id'],
                    'quantity' => $lineData['quantity'],
                    'unit_price' => $lineData['unit_price'] ?? $product->cost_price,
                    'discount_percent' => $lineData['discount_percent'] ?? 0,
                    'tax_percent' => $lineData['tax_percent'] ?? $product->tax_rate ?? 0,
                    'unit_id' => $lineData['unit_id'] ?? $product->unit_id,
                    'description' => $lineData['description'] ?? null,
                ]);
            }

            return $po->fresh(['lines', 'supplier']);
        });
    }

    /**
     * Approve a purchase order
     */
    public function approvePurchaseOrder(PurchaseOrder $po): bool
    {
        return $po->approve(auth()->id());
    }

    // ========================================
    // Goods Receipt Note Operations
    // ========================================

    /**
     * Receive goods from a purchase order
     */
    public function receiveGoods(
        PurchaseOrder $po,
        Warehouse $warehouse,
        array $receivedItems,
        ?string $supplierDeliveryNote = null,
        ?string $notes = null
    ): Grn {
        if (!$po->canReceive()) {
            throw new \RuntimeException("Cannot receive goods for PO in status: {$po->status->label()}");
        }

        return DB::transaction(function () use ($po, $warehouse, $receivedItems, $supplierDeliveryNote, $notes) {
            // Create GRN
            $grn = Grn::create([
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'warehouse_id' => $warehouse->id,
                'received_date' => now(),
                'status' => GrnStatus::DRAFT,
                'supplier_delivery_note' => $supplierDeliveryNote,
                'notes' => $notes,
                'received_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]);

            $totalValue = 0;

            foreach ($receivedItems as $item) {
                $poLine = PurchaseOrderLine::find($item['purchase_order_line_id']);
                if (!$poLine || $poLine->purchase_order_id !== $po->id) {
                    continue;
                }

                $product = $poLine->product;
                $quantity = min($item['quantity'], $poLine->getRemainingQuantity());
                $unitCost = $item['unit_cost'] ?? $poLine->unit_price;

                // Add stock via InventoryService
                $stockMovement = $this->inventoryService->addStock(
                    $product,
                    $warehouse,
                    $quantity,
                    $unitCost,
                    MovementType::PURCHASE,
                    $grn->grn_number,
                    "GRN from PO: {$po->po_number}",
                    Grn::class,
                    $grn->id
                );

                // Create GRN line
                $grnLine = GrnLine::create([
                    'grn_id' => $grn->id,
                    'purchase_order_line_id' => $poLine->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'stock_movement_id' => $stockMovement->id,
                ]);

                // Update PO line received quantity
                $poLine->addReceivedQuantity($quantity);

                $totalValue += $quantity * $unitCost;
            }

            // Complete GRN
            $grn->complete();

            // Create journal entry for inventory DR, GRN Clearing CR
            $this->createGrnJournalEntry($grn, $totalValue);

            return $grn->fresh(['lines', 'purchaseOrder', 'warehouse']);
        });
    }

    /**
     * Create journal entry for GRN
     */
    protected function createGrnJournalEntry(Grn $grn, float $totalValue): void
    {
        $inventoryAccount = Account::where('code', '1301')->first(); // Inventory
        // Use GRN Clearing Account (2120) instead of AP directly
        $clearingAccount = Account::where('code', '2120')->first();

        if (!$inventoryAccount || !$clearingAccount) {
            return; // Skip if accounts not configured
        }

        $entry = $this->journalService->create([
            'entry_date' => $grn->received_date,
            'reference' => $grn->grn_number,
            'description' => "Goods Receipt from {$grn->supplier->name} (Pending Invoice)",
            'source_type' => Grn::class,
            'source_id' => $grn->id,
        ], [
            ['account_id' => $inventoryAccount->id, 'debit' => $totalValue, 'credit' => 0],
            ['account_id' => $clearingAccount->id, 'debit' => 0, 'credit' => $totalValue],
        ]);

        $grn->update(['journal_entry_id' => $entry->id]);
    }

    // ========================================
    // Purchase Invoice Operations
    // ========================================

    /**
     * Create purchase invoice from GRN
     */
    public function createInvoiceFromGrn(
        Grn $grn,
        ?string $supplierInvoiceNumber = null,
        ?Carbon $invoiceDate = null,
        ?Carbon $dueDate = null
    ): PurchaseInvoice {
        return DB::transaction(function () use ($grn, $supplierInvoiceNumber, $invoiceDate, $dueDate) {
            $supplier = $grn->supplier;
            $paymentTerms = $supplier->payment_terms ?? 30;
            $invoiceDate = $invoiceDate ?? now();
            $dueDate = $dueDate ?? $invoiceDate->copy()->addDays($paymentTerms);

            $invoice = PurchaseInvoice::create([
                'supplier_invoice_number' => $supplierInvoiceNumber,
                'supplier_id' => $supplier->id,
                'grn_id' => $grn->id,
                'purchase_order_id' => $grn->purchase_order_id,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'status' => PurchaseInvoiceStatus::APPROVED, // Auto-approve for now since it matches GRN
                'created_by' => auth()->id(),
            ]);

            $totalValue = 0;

            // Copy lines from GRN
            foreach ($grn->lines as $grnLine) {
                $invoice->lines()->create([
                    'product_id' => $grnLine->product_id,
                    'description' => $grnLine->product->name,
                    'quantity' => $grnLine->quantity,
                    'unit_price' => $grnLine->unit_cost,
                    'tax_percent' => $grnLine->product->tax_rate ?? 0,
                ]);

                $totalValue += $grnLine->quantity * $grnLine->unit_cost;
            }

            // Create Journal Entry: DR GRN Clearing, CR Accounts Payable
            $this->createInvoiceJournalEntry($invoice, $totalValue);

            return $invoice->fresh(['lines', 'supplier']);
        });
    }

    /**
     * Create journal entry for Purchase Invoice
     */
    protected function createInvoiceJournalEntry(PurchaseInvoice $invoice, float $totalValue): void
    {
        $clearingAccount = Account::where('code', '2120')->first(); // GRN Clearing
        $apAccount = Account::where('code', '2101')->first(); // Accounts Payable

        if (!$clearingAccount || !$apAccount) {
            return;
        }

        $entry = $this->journalService->create([
            'entry_date' => $invoice->invoice_date,
            'reference' => $invoice->invoice_number,
            'description' => "Purchase Invoice #{$invoice->supplier_invoice_number} from {$invoice->supplier->name}",
            'source_type' => PurchaseInvoice::class,
            'source_id' => $invoice->id,
        ], [
            ['account_id' => $clearingAccount->id, 'debit' => $totalValue, 'credit' => 0],
            ['account_id' => $apAccount->id, 'debit' => 0, 'credit' => $totalValue],
        ]);

        $invoice->update(['journal_entry_id' => $entry->id]);
    }

    // ========================================
    // Supplier Payment Operations
    // ========================================

    /**
     * Create supplier payment
     */
    public function createPayment(
        Supplier $supplier,
        float $amount,
        Account $paymentAccount,
        ?string $paymentMethod = 'bank_transfer',
        ?string $reference = null,
        ?Carbon $paymentDate = null,
        array $invoiceAllocations = []
    ): SupplierPayment {
        return DB::transaction(function () use ($supplier, $amount, $paymentAccount, $paymentMethod, $reference, $paymentDate, $invoiceAllocations) {
            $payment = SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'payment_date' => $paymentDate ?? now(),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'payment_account_id' => $paymentAccount->id,
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);

            // Allocate to invoices
            foreach ($invoiceAllocations as $allocation) {
                // Ensure allocation is an array (could be string or object)
                if (is_string($allocation)) {
                    $allocation = json_decode($allocation, true) ?? [];
                }
                if (!is_array($allocation) || empty($allocation['invoice_id'])) {
                    continue;
                }

                $invoice = PurchaseInvoice::find($allocation['invoice_id']);
                if ($invoice && $invoice->supplier_id === $supplier->id) {
                    $allocAmount = min($allocation['amount'] ?? 0, $invoice->balance_due);
                    if ($allocAmount > 0) {
                        $payment->allocateToInvoice($invoice, $allocAmount);
                    }
                }
            }


            // Create journal entry: DR AP, CR Cash/Bank
            $this->createPaymentJournalEntry($payment, $supplier);

            return $payment->fresh(['supplier', 'allocations']);
        });
    }

    /**
     * Create journal entry for supplier payment
     */
    protected function createPaymentJournalEntry(SupplierPayment $payment, Supplier $supplier): void
    {
        $apAccount = $supplier->account_id
            ? Account::find($supplier->account_id)
            : Account::where('code', '2101')->first();

        if (!$apAccount) {
            return;
        }

        $entry = $this->journalService->create([
            'entry_date' => $payment->payment_date,
            'reference' => $payment->payment_number,
            'description' => "Payment to {$supplier->name}",
            'source_type' => SupplierPayment::class,
            'source_id' => $payment->id,
        ], [
            ['account_id' => $apAccount->id, 'debit' => $payment->amount, 'credit' => 0],
            ['account_id' => $payment->payment_account_id, 'debit' => 0, 'credit' => $payment->amount],
        ]);

        $payment->update(['journal_entry_id' => $entry->id]);
    }
}
