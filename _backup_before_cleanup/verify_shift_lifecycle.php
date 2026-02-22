<?php

use App\Models\PosShift;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function testShiftLifecycle()
{
    echo "Testing Shift Lifecycle...\n";
    $user = User::first();
    auth()->login($user);

    // 1. Force close any open shifts
    PosShift::where('user_id', $user->id)->update(['status' => 'closed', 'closed_at' => now()]);

    // 2. Open new shift
    $shift = PosShift::openNewShift(150.50);
    if ($shift->opening_cash == 150.50 && $shift->status === 'open') {
        echo "✓ openNewShift works correctly.\n";
    }

    // 3. Get Active Shift
    $active = PosShift::getActiveShift();
    if ($active && $active->id === $shift->id) {
        echo "✓ getActiveShift detected new shift.\n";
    }

    // 4. Test xReport data structure via Controller (internal call simulation)
    $controller = app()->make(\App\Http\Controllers\POSController::class);
    $response = $controller->xReport();
    $data = $response->getData();

    if ($data->success && $data->data->shift_id === $shift->id) {
        echo "✓ xReport returns correct shift data.\n";
    } else {
        echo "✗ xReport FAILED to find shift.\n";
    }
}

try {
    DB::beginTransaction();
    testShiftLifecycle();
    DB::rollBack();
    echo "\nVerification Complete!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
