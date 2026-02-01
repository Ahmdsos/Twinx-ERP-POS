<?php

use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Enums\JournalStatus;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running POSTFIX...\n";

// Batch update for speed and reliability
$count = JournalEntry::where('status', 'draft')->update(['status' => 'posted']);

if ($count > 0) {
    echo "✅ Executed batch update for {$count} draft entries.\n";
} else {
    echo "✅ No draft entries pending.\n";
}

// Verification
$remaining = JournalEntry::where('status', 'draft')->count();
if ($remaining > 0) {
    echo "⚠️ Warning: {$remaining} entries are still in draft state!\n";
} else {
    echo "🎉 All entries are POSTED and ready for reporting.\n";
}
