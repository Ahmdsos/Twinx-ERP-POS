<?php

use Modules\Accounting\Models\Account;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cash = Account::where('code', '1101')->first();
echo "Current Balance: " . $cash->balance . "\n";

echo "Attempting to save -100.00...\n";
$cash->balance = -100.00;
$cash->save();

$cash->refresh();
echo "New Balance: " . $cash->balance . "\n";

if ($cash->balance == -100.00) {
    echo "✅ Success: Account accepts negative balance.\n";
} else {
    echo "❌ Failure: Account rejected negative balance.\n";
}
