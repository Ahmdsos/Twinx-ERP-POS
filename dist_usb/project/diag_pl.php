<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ProductStock;

echo "=== STOCK MOVEMENT DIAGNOSTIC ===\n\n";

echo "--- Products ---\n";
$products = Product::all();
foreach ($products as $p) {
    echo "  #{$p->id} {$p->name} | cost_price={$p->cost_price} | selling_price={$p->selling_price}\n";
    $stocks = ProductStock::where('product_id', $p->id)->get();
    foreach ($stocks as $s) {
        echo "    Stock WH#{$s->warehouse_id}: qty={$s->quantity} | avg_cost={$s->average_cost} | total_value={$s->total_value}\n";
    }
}

echo "\n--- All Stock Movements ---\n";
$movements = StockMovement::orderBy('id')->get();
foreach ($movements as $m) {
    $type = is_object($m->type) ? $m->type->value : $m->type;
    echo "  SM#{$m->id} | type={$type} | qty={$m->quantity} | unit_cost={$m->unit_cost} | total_cost={$m->total_cost} | remaining={$m->remaining_quantity} | ref={$m->reference}\n";
}

echo "\n--- POS Sale removeStock calls ---\n";
// Check how POS calls removeStock
$salesMvts = StockMovement::where('type', 'sale')->orWhere('type', 'SALE')->get();
echo "  Sale movements: {$salesMvts->count()}\n";
foreach ($salesMvts as $m) {
    echo "  SM#{$m->id} | qty={$m->quantity} | unit_cost={$m->unit_cost} | total_cost={$m->total_cost} | JE={$m->journal_entry_id}\n";
}

echo "\n=== END ===\n";
