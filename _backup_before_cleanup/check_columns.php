<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('sales_returns');
print_r($columns);

$hasShiftId = \Illuminate\Support\Facades\Schema::hasColumn('sales_returns', 'shift_id');
echo "Has shift_id: " . ($hasShiftId ? 'YES' : 'NO') . "\n";
