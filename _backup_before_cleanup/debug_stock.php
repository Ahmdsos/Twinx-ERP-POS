<?php

use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;
use Modules\Inventory\Models\Warehouse;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- WAREHOUSES ---\n";
foreach (Warehouse::all() as $w) {
    echo "ID: {$w->id} - Name: {$w->name}\n";
}

echo "\n--- PRODUCT STOCK (All) ---\n";
$stocks = ProductStock::with(['product', 'warehouse'])->where('quantity', '>', 0)->get();
foreach ($stocks as $s) {
    echo "Product: {$s->product->name} (ID: {$s->product_id}) | Warehouse: {$s->warehouse->name} (ID: {$s->warehouse_id}) | Qty: {$s->quantity} | Reserved: {$s->reserved_quantity} | Avail: {$s->available_quantity}\n";
}

echo "\n--- CHECKING PRODUCT ID 677 (from logs?) ---\n";
// The previous log showed recent transactions for product 677? Or I can just list all.
// Let's just rely on the list above.
