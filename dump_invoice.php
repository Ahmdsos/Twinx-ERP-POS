<?php
use Modules\Sales\Models\SalesInvoice;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoice = SalesInvoice::with('lines.product')->latest()->first();
if (!$invoice) {
    echo "No invoice found.\n";
    exit;
}

echo "Invoice #{$invoice->invoice_number}\n";
echo "Subtotal: {$invoice->subtotal}\n";
echo "Tax: {$invoice->tax_amount}\n";
echo "Total: {$invoice->total}\n";
echo "Settings: tax_inclusive=" . \App\Models\Setting::getValue('tax_inclusive', 'false') . "\n";

foreach ($invoice->lines as $line) {
    echo "--- Line ---\n";
    echo "Product: {$line->product->name} (Price in DB: {$line->product->selling_price})\n";
    echo "Qty: {$line->quantity}\n";
    echo "Unit Price (Line): {$line->unit_price}\n";
    echo "Subtotal (Line): {$line->subtotal}\n";
    echo "Tax (Line): {$line->tax_amount}\n";
    echo "Total (Line): {$line->line_total}\n";
}
