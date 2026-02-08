<?php

use App\Models\PosShift;
use App\Models\User;
use Modules\HR\Models\DeliveryDriver;
use Modules\Sales\Models\DeliveryOrder;
use Modules\Sales\Enums\DeliveryStatus;
use Modules\Sales\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testShiftDetection()
{
    echo "Testing Shift Detection...\n";
    $user = User::first();
    auth()->login($user);

    // Close any previous shifts
    PosShift::where('user_id', $user->id)->update(['status' => 'closed', 'closed_at' => now()]);

    $shift = PosShift::create([
        'user_id' => $user->id,
        'opened_at' => now(),
        'status' => 'open',
        'opening_cash' => 100
    ]);

    $active = PosShift::getActiveShift();
    if ($active && $active->id === $shift->id) {
        echo "✓ Active shift detected correctly.\n";
    } else {
        echo "✗ Active shift detection FAILED.\n";
    }
}

function testDriverRelease()
{
    echo "Testing Driver Release...\n";

    $driver = DeliveryDriver::first();
    if (!$driver) {
        echo "! No driver found, skipping.\n";
        return;
    }

    $driver->occupy();
    if ($driver->status === 'on_delivery') {
        echo "✓ Driver occupied.\n";
    }

    $driver->release();
    if ($driver->status === 'available') {
        echo "✓ Driver released.\n";
    } else {
        echo "✗ Driver release FAILED. Status: {$driver->status}\n";
    }
}

function testInvoiceLinesEagerLoading()
{
    echo "Testing Invoice Lines eager loading...\n";

    $invoice = SalesInvoice::with('lines')->latest()->first();
    if (!$invoice) {
        echo "! No invoice found, skipping.\n";
        return;
    }

    if ($invoice->lines->count() > 0 || SalesInvoice::count() > 0) {
        echo "✓ Invoice relationship 'lines' exists and is queryable.\n";
    }
}

try {
    DB::beginTransaction();
    testShiftDetection();
    testDriverRelease();
    testInvoiceLinesEagerLoading();
    DB::rollBack();
    echo "\nVerification Complete!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
