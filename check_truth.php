<?php
use App\Models\Setting;
use App\Models\PosShift;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- SETTINGS ---\n";
echo "tax_inclusive: " . Setting::getValue('tax_inclusive', 'NOT SET (default: false)') . "\n";
echo "default_tax_rate: " . Setting::getValue('default_tax_rate', 'NOT SET (default: 14)') . "\n";

echo "\n--- ACTIVE SHIFT ---\n";
$shift = PosShift::where('status', 'open')->latest()->first();
if ($shift) {
    echo "ID: " . $shift->id . "\n";
    echo "User: " . $shift->user_id . "\n";
    echo "Total Sales (Count): " . $shift->total_sales . "\n";
    echo "Total Amount (Value): " . $shift->total_amount . "\n";
    echo "Total Cash: " . $shift->total_cash . "\n";

    echo "\n--- INVOICES IN SHIFT ---\n";
    $invoices = DB::table('sales_invoices')->where('pos_shift_id', $shift->id)->get();
    foreach ($invoices as $inv) {
        echo "Invoice #{$inv->invoice_number} | Total: {$inv->total} | Shift: {$inv->pos_shift_id}\n";
    }
} else {
    echo "No open shift found.\n";
}
