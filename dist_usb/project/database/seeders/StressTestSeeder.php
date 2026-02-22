<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Warehouse;
use Modules\Sales\Models\Customer;
use Modules\Purchasing\Models\Supplier;
use Modules\Purchasing\Models\PurchaseOrder;
use Modules\Purchasing\Models\PurchaseOrderLine;
use Modules\Sales\Models\SalesInvoice;
use Modules\Sales\Models\SalesInvoiceLine;
use Modules\Inventory\Services\InventoryService;
use Modules\Accounting\Services\JournalService;
use Modules\Inventory\Enums\MovementType;
use Modules\Sales\Enums\SalesInvoiceStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;

class StressTestSeeder extends Seeder
{
    protected InventoryService $inventoryService;
    protected JournalService $journalService;

    public function __construct(InventoryService $inventoryService, JournalService $journalService)
    {
        $this->inventoryService = $inventoryService;
        $this->journalService = $journalService;
    }

    public function run(): void
    {
        $this->command->info('🚀 Starting Stress Test Seeder...');

        // Verify Accounts exist first
        if (Account::count() === 0) {
            $this->call(\Database\Seeders\ChartOfAccountsSeeder::class);
        }

        // Ensure Admin User exists (ID 1)
        if (\App\Models\User::count() === 0) {
            \App\Models\User::create([
                'name' => 'Admin User',
                'email' => 'admin@twinx.com',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]);
        }

        // Ensure Master Data exists
        $this->call([
            \Database\Seeders\InventorySeeder::class,
            \Database\Seeders\SalesSeeder::class,
            \Database\Seeders\PurchasingSeeder::class,
        ]);

        $warehouses = Warehouse::all();
        $products = Product::all();
        $customers = Customer::all();
        $suppliers = Supplier::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found! Seeding failed.');
            return;
        }

        // 1. Create Opening Balance / Purchases (To add stock)
        $this->command->info('📦 Creating 20 Purchase Orders (Stock In)...');
        foreach ($suppliers as $index => $supplier) {
            for ($i = 0; $i < 4; $i++) {
                $this->createPurchaseCycle($supplier, $products, $warehouses->random());
            }
        }

        // 2. Create Sales (POS & Invoices)
        $this->command->info('💰 Creating 50 POS Sales Transactions...');
        for ($i = 0; $i < 50; $i++) {
            $this->createPosSale($products, $warehouses->random());
        }

        $this->command->info('🏢 Creating 30 B2B Invoices...');
        for ($i = 0; $i < 30; $i++) {
            $this->createB2BSale($customers->random(), $products, $warehouses->random());
        }

        $this->command->info('✅ Stress Test Seeding Complete!');
    }

    protected function createPurchaseCycle($supplier, $products, $warehouse)
    {
        DB::transaction(function () use ($supplier, $products, $warehouse) {
            $date = Carbon::now()->subDays(rand(1, 60));

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . strtoupper(uniqid()),
                'supplier_id' => $supplier->id,
                'order_date' => $date,
                'status' => 'received',
                'subtotal' => 0,
                'total' => 0,
                'warehouse_id' => $warehouse->id,
            ]);

            $productCount = $products->count();
            $requestCount = rand(1, min($productCount, 5));
            $selectedProducts = $products->random($requestCount);

            $total = 0;

            foreach ($selectedProducts as $product) {
                // Large quantity to ensure we have stock for sales
                $qty = rand(100, 1000);
                $cost = $product->cost_price > 0 ? $product->cost_price : rand(100, 5000);
                $lineTotal = $qty * $cost;

                PurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'received_quantity' => $qty,
                    'unit_price' => $cost,
                    'line_total' => $lineTotal,
                    'unit_id' => $product->purchase_unit_id ?? $product->unit_id,
                ]);

                $total += $lineTotal;

                $this->inventoryService->addStock(
                    $product,
                    $warehouse,
                    (float) $qty,
                    (float) $cost,
                    MovementType::PURCHASE,
                    $po->po_number,
                    'Stress Test Seeding',
                    PurchaseOrder::class,
                    $po->id
                );
            }

            $po->update(['total' => $total, 'subtotal' => $total]);

            // Create Purchase Invoice for the PO
            \Modules\Purchasing\Models\PurchaseInvoice::create([
                'invoice_number' => 'PINV-' . str_replace('PO-', '', $po->po_number),
                'supplier_id' => $supplier->id,
                'invoice_date' => $po->order_date,
                'due_date' => $po->order_date->copy()->addDays(30),
                'status' => 'paid',
                'subtotal' => $total,
                'total' => $total,
                'paid_amount' => $total,
                'purchase_order_id' => $po->id, // If linking column exists
                'notes' => 'Auto-generated invoice from Stress Test',
            ]);
        });
    }

    protected function createPosSale($products, $warehouse)
    {
        DB::transaction(function () use ($products, $warehouse) {
            $date = Carbon::now()->subDays(rand(0, 30))->addHours(rand(8, 20));

            // Get Walk-In Customer
            $walkInCustomer = Customer::firstOrCreate(
                ['code' => 'WALK-IN'],
                [
                    'name' => 'Walk-in Customer',
                    'phone' => 'N/A',
                    'is_active' => true,
                    'created_by' => 1,
                ]
            );

            $selectedProducts = $products->random(rand(1, 5));
            $total = 0;

            // Generate Invoice ID
            $invoice = SalesInvoice::create([
                'invoice_number' => 'POS-' . strtoupper(uniqid()),
                'customer_id' => $walkInCustomer->id,
                'invoice_date' => $date,
                'due_date' => $date,
                'status' => SalesInvoiceStatus::PAID,
                'subtotal' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'source' => 'pos'
            ]);

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 5);
                $price = $product->selling_price > 0 ? $product->selling_price : rand(200, 8000);
                $lineTotal = $qty * $price;

                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                    'tax_amount' => 0,
                    'discount_amount' => 0
                ]);

                $total += $lineTotal;

                try {
                    $this->inventoryService->removeStock(
                        $product,
                        $warehouse,
                        (float) $qty,
                        MovementType::SALE,
                        $invoice->invoice_number,
                        'POS Stress Test',
                        SalesInvoice::class,
                        $invoice->id
                    );
                } catch (\Exception $e) {
                    // Ignore negative stock blocking for seeding
                }
            }

            $invoice->update([
                'subtotal' => $total,
                'total' => $total,
                'paid_amount' => $total,
            ]);

            $this->createSalesJournal($invoice, true);
        });
    }

    protected function createB2BSale($customer, $products, $warehouse)
    {
        DB::transaction(function () use ($customer, $products, $warehouse) {
            $date = Carbon::now()->subDays(rand(0, 45))->addHours(rand(9, 17));
            $isPaid = (bool) rand(0, 1);

            $selectedProducts = $products->random(rand(2, 6));
            $total = 0;

            $invoice = SalesInvoice::create([
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'customer_id' => $customer->id,
                'invoice_date' => $date,
                'due_date' => $date->copy()->addDays(30),
                'status' => $isPaid ? SalesInvoiceStatus::PAID : SalesInvoiceStatus::PENDING,
                'subtotal' => 0,
                'total' => 0,
                'paid_amount' => 0,
                'source' => 'manual'
            ]);

            foreach ($selectedProducts as $product) {
                $qty = rand(5, 20);
                $price = $product->selling_price;
                $lineTotal = $qty * $price;

                SalesInvoiceLine::create([
                    'sales_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => $lineTotal,
                    'tax_amount' => 0,
                    'discount_amount' => 0
                ]);

                $total += $lineTotal;

                try {
                    $this->inventoryService->removeStock(
                        $product,
                        $warehouse,
                        (float) $qty,
                        MovementType::SALE,
                        $invoice->invoice_number,
                        'B2B Sale',
                        SalesInvoice::class,
                        $invoice->id
                    );
                } catch (\Exception $e) {
                }
            }

            $invoice->update([
                'subtotal' => $total,
                'total' => $total,
                'paid_amount' => $isPaid ? $total : 0,
            ]);

            $this->createSalesJournal($invoice, $isPaid);
        });
    }

    protected function createSalesJournal($invoice, $isPaid)
    {
        // 1101 Cash, 1201 AR, 4101 Sales Rev
        $cashAcc = Account::where('code', '1101')->firstOrFail()->id;
        $arAcc = Account::where('code', '1201')->firstOrFail()->id;
        $salesAcc = Account::where('code', '4101')->firstOrFail()->id;

        $drAcc = $isPaid ? $cashAcc : $arAcc;

        $entry = $this->journalService->create([
            'entry_date' => $invoice->invoice_date,
            'reference' => $invoice->invoice_number,
            'description' => 'Sales Invoice ' . $invoice->invoice_number,
            'source_type' => SalesInvoice::class,
            'source_id' => $invoice->id,
        ], [
            ['account_id' => $drAcc, 'debit' => $invoice->total, 'credit' => 0],
            ['account_id' => $salesAcc, 'debit' => 0, 'credit' => $invoice->total]
        ]);

        $this->journalService->post($entry);
    }
}
