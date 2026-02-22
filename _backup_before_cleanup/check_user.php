<?php
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = User::first();
echo "User: {$user->name} (ID: {$user->id})\n";
echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
echo "Permissions (direct): " . $user->getPermissionNames()->implode(', ') . "\n";
echo "Can sales.manage? " . ($user->can('sales.manage') ? 'YES' : 'NO') . "\n";
echo "Can viewAny-returns? " . ($user->can('viewAny-returns') ? 'YES' : 'NO') . "\n";
