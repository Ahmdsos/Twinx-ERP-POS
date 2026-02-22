<?php
/**
 * TWINX ERP — Comprehensive System Audit Script
 * Scans the entire project for potential runtime errors:
 *   1. Route → Controller → Method mapping
 *   2. Model fillable vs DB column mismatches
 *   3. Blade view references
 *   4. Wrong class imports / missing classes
 *   5. DB schema completeness
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

$issues = [];
$warnings = [];

echo "╔══════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   TWINX ERP — COMPREHENSIVE SYSTEM AUDIT            ║" . PHP_EOL;
echo "╚══════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

// ================================================================
// 1. DATABASE SCHEMA AUDIT
// ================================================================
echo "▶ [1/6] Database Schema Audit..." . PHP_EOL;

$driver = config('database.default');
echo "   DB Driver: $driver" . PHP_EOL;

if ($driver === 'sqlite') {
    $allTables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))->pluck('name');
} else {
    $allTables = collect(DB::select('SHOW TABLES'))->map(fn($t) => array_values((array) $t)[0]);
}

echo "   Tables found: " . $allTables->count() . PHP_EOL;

// Check critical tables
$requiredTables = [
    'users',
    'pos_shifts',
    'sales_invoices',
    'sales_invoice_lines',
    'products',
    'categories',
    'warehouses',
    'stock_movements',
    'product_stock',
    'accounts',
    'journal_entries',
    'journal_entry_lines',
    'purchase_orders',
    'purchase_order_lines',
    'suppliers',
    'hr_employees',
    'expenses',
    'expense_categories',
    'customers',
    'sales_returns',
    'sales_return_lines',
    'security_audit_logs',
    'delivery_orders',
    'delivery_order_lines',
    'sales_orders',
    'sales_order_lines',
    'settings',
];

foreach ($requiredTables as $table) {
    if (!$allTables->contains($table)) {
        $issues[] = "[DB] Missing required table: $table";
    }
}

// Check critical columns
$columnChecks = [
    'pos_shifts' => ['id', 'user_id', 'opened_at', 'closed_at', 'opening_cash', 'closing_cash', 'status', 'total_sales', 'total_amount', 'total_cash', 'total_card'],
    'sales_invoices' => ['id', 'invoice_number', 'customer_id', 'pos_shift_id', 'delivery_fee', 'is_delivery', 'driver_id', 'warehouse_id', 'total', 'paid_amount', 'balance_due', 'status', 'shipping_address', 'source'],
    'sales_invoice_lines' => ['id', 'sales_invoice_id', 'product_id', 'quantity', 'unit_price', 'line_total'],
    'expenses' => ['id', 'pos_shift_id', 'amount', 'status', 'category_id', 'payment_account_id', 'user_id'],
    'products' => ['id', 'name', 'sku', 'barcode', 'selling_price', 'cost_price', 'category_id', 'is_active'],
    'security_audit_logs' => ['id', 'user_id', 'shift_id', 'event_type', 'severity', 'description', 'metadata'],
    'sales_returns' => ['id', 'return_number', 'sales_invoice_id', 'total_amount', 'status', 'shift_id'],
    'customers' => ['id', 'name', 'code', 'phone'],
    'suppliers' => ['id', 'name', 'code'],
    'accounts' => ['id', 'name', 'code', 'type', 'balance'],
    'journal_entries' => ['id', 'entry_number', 'entry_date', 'status', 'total_debit', 'total_credit'],
    'journal_entry_lines' => ['id', 'journal_entry_id', 'account_id', 'debit', 'credit'],
    'hr_employees' => ['id', 'employee_code', 'first_name', 'last_name', 'basic_salary', 'status'],
    'delivery_orders' => ['id', 'do_number', 'status', 'driver_id', 'sales_invoice_id'],
    'warehouses' => ['id', 'name', 'code', 'is_default'],
    'stock_movements' => ['id', 'product_id', 'warehouse_id', 'quantity', 'type'],
    'product_stock' => ['id', 'product_id', 'warehouse_id', 'quantity', 'available_quantity'],
    'purchase_orders' => ['id', 'po_number', 'supplier_id', 'status', 'total'],
    'hr_payrolls' => ['id', 'month', 'year', 'status', 'journal_entry_id'],
    'hr_payroll_items' => ['id', 'payroll_id', 'employee_id', 'basic_salary', 'net_salary', 'advance_deductions'],
];

foreach ($columnChecks as $table => $cols) {
    if (!Schema::hasTable($table))
        continue;
    $existing = Schema::getColumnListing($table);
    foreach ($cols as $col) {
        if (!in_array($col, $existing)) {
            $issues[] = "[DB] Missing column: $table.$col";
        }
    }
}

// ================================================================
// 2. ROUTE → CONTROLLER AUDIT
// ================================================================
echo "▶ [2/6] Route → Controller Audit..." . PHP_EOL;

$routes = Route::getRoutes();
$routeCount = 0;
$routeErrors = 0;

foreach ($routes as $route) {
    $action = $route->getAction();
    $routeCount++;

    if (isset($action['controller'])) {
        $parts = explode('@', $action['controller']);
        $controllerClass = $parts[0];
        $method = $parts[1] ?? '__invoke';

        if (!class_exists($controllerClass)) {
            $issues[] = "[ROUTE] Controller class not found: $controllerClass (route: {$route->uri()})";
            $routeErrors++;
        } elseif (!method_exists($controllerClass, $method)) {
            $issues[] = "[ROUTE] Method not found: {$controllerClass}@{$method} (route: {$route->uri()})";
            $routeErrors++;
        }
    }
}

echo "   Total routes: $routeCount, Route errors: $routeErrors" . PHP_EOL;

// ================================================================
// 3. MODEL FILLABLE vs DB COLUMNS AUDIT
// ================================================================
echo "▶ [3/6] Model Fillable vs DB Columns Audit..." . PHP_EOL;

$modelDirs = [
    __DIR__ . '/Modules/Sales/Models',
    __DIR__ . '/Modules/Inventory/Models',
    __DIR__ . '/Modules/Accounting/Models',
    __DIR__ . '/Modules/Purchasing/Models',
    __DIR__ . '/Modules/HR/Models',
    __DIR__ . '/Modules/Finance/Models',
    __DIR__ . '/Modules/Core/Models',
    __DIR__ . '/Modules/Reporting/Models',
];

foreach ($modelDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob("$dir/*.php") as $file) {
        $content = file_get_contents($file);

        // Extract namespace and class
        if (
            preg_match('/namespace\s+([\w\\\\]+);/', $content, $nsMatch) &&
            preg_match('/class\s+(\w+)/', $content, $classMatch)
        ) {

            $fqcn = $nsMatch[1] . '\\' . $classMatch[1];

            if (!class_exists($fqcn))
                continue;

            try {
                $instance = new $fqcn();
                $table = $instance->getTable();

                if (!Schema::hasTable($table)) {
                    $issues[] = "[MODEL] Table '$table' not found for model $fqcn";
                    continue;
                }

                $dbCols = Schema::getColumnListing($table);
                $fillable = $instance->getFillable();

                foreach ($fillable as $col) {
                    if (!in_array($col, $dbCols)) {
                        $issues[] = "[MODEL] Fillable '$col' not in DB table '$table' (Model: $fqcn)";
                    }
                }
            } catch (\Throwable $e) {
                $warnings[] = "[MODEL] Cannot instantiate $fqcn: " . $e->getMessage();
            }
        }
    }
}

// ================================================================
// 4. BLADE VIEW EXISTENCE AUDIT
// ================================================================
echo "▶ [4/6] Blade View References Audit..." . PHP_EOL;

$viewErrors = 0;
$controllerDirs = [
    __DIR__ . '/Modules/Sales/Http/Controllers',
    __DIR__ . '/Modules/Inventory/Http/Controllers',
    __DIR__ . '/Modules/Accounting/Http/Controllers',
    __DIR__ . '/Modules/Purchasing/Http/Controllers',
    __DIR__ . '/Modules/HR/Http/Controllers',
    __DIR__ . '/Modules/Finance/Http/Controllers',
    __DIR__ . '/Modules/Core/Http/Controllers',
    __DIR__ . '/Modules/Reporting/Http/Controllers',
    __DIR__ . '/app/Http/Controllers',
];

foreach ($controllerDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob("$dir/*.php") as $file) {
        $content = file_get_contents($file);
        // Find view() calls
        if (preg_match_all("/view\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            foreach ($matches[1] as $viewName) {
                $found = false;

                // Handle module namespace (e.g., hr::employees.index)
                if (str_contains($viewName, '::')) {
                    [$ns, $viewPart] = explode('::', $viewName, 2);
                    $viewPath = str_replace('.', '/', $viewPart);

                    // Map namespace to module directory (case-insensitive)
                    $moduleMap = [
                        'hr' => 'HR',
                        'sales' => 'Sales',
                        'inventory' => 'Inventory',
                        'accounting' => 'Accounting',
                        'purchasing' => 'Purchasing',
                        'finance' => 'Finance',
                        'core' => 'Core',
                        'reporting' => 'Reporting',
                    ];

                    $moduleName = $moduleMap[strtolower($ns)] ?? ucfirst($ns);
                    $paths = [
                        __DIR__ . "/Modules/$moduleName/resources/views/$viewPath.blade.php",
                        __DIR__ . "/Modules/$moduleName/resources/views/$viewPath.php",
                    ];
                } else {
                    $viewPath = str_replace('.', '/', $viewName);

                    // Check standard paths
                    $paths = [
                        __DIR__ . "/resources/views/$viewPath.blade.php",
                        __DIR__ . "/resources/views/$viewPath.php",
                    ];

                    // Also check all module paths
                    foreach (['Sales', 'Inventory', 'Accounting', 'Purchasing', 'HR', 'Finance', 'Core', 'Reporting'] as $mod) {
                        $paths[] = __DIR__ . "/Modules/$mod/resources/views/$viewPath.blade.php";
                    }
                }

                foreach ($paths as $p) {
                    if (file_exists($p)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    // Don't flag component views or mail views
                    if (!str_starts_with($viewName, 'components.') && !str_starts_with($viewName, 'mail.')) {
                        $warnings[] = "[VIEW] View not found: '$viewName' (referenced in " . basename($file) . ")";
                        $viewErrors++;
                    }
                }
            }
        }
    }
}

echo "   View warnings: $viewErrors" . PHP_EOL;

// ================================================================
// 5. PHP CLASS IMPORT AUDIT
// ================================================================
echo "▶ [5/6] PHP Class Import Audit..." . PHP_EOL;

$phpFiles = [];
$scanDirs = [
    __DIR__ . '/Modules',
    __DIR__ . '/app',
];

foreach ($scanDirs as $scanDir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanDir));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $phpFiles[] = $file->getPathname();
        }
    }
}

$importErrors = 0;
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);

    // Check 'use' imports
    if (preg_match_all('/^use\s+([\w\\\\]+);/m', $content, $matches)) {
        foreach ($matches[1] as $className) {
            // Skip framework/vendor classes
            if (
                str_starts_with($className, 'Illuminate\\') ||
                str_starts_with($className, 'Spatie\\') ||
                str_starts_with($className, 'Carbon\\') ||
                str_starts_with($className, 'Barryvdh\\') ||
                str_starts_with($className, 'Maatwebsite\\') ||
                str_starts_with($className, 'Intervention\\') ||
                str_starts_with($className, 'Laravel\\')
            )
                continue;

            // Check if it's an App or Modules class
            if (str_starts_with($className, 'App\\') || str_starts_with($className, 'Modules\\')) {
                if (!class_exists($className) && !interface_exists($className) && !trait_exists($className) && !enum_exists($className)) {
                    $issues[] = "[IMPORT] Class not found: '$className' (in " . basename($file) . ")";
                    $importErrors++;
                }
            }
        }
    }
}

echo "   Import errors: $importErrors" . PHP_EOL;

// ================================================================
// 6. INLINE CLASS REF AUDIT (e.g. \App\Models\X::)
// ================================================================
echo "▶ [6/6] Inline Class Reference Audit..." . PHP_EOL;

$inlineErrors = 0;
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);

    // Find \App\Models\Something or \Modules\...\Models\Something
    if (preg_match_all('/\\\\(App\\\\Models\\\\[A-Z]\w+)/', $content, $matches)) {
        foreach (array_unique($matches[1]) as $className) {
            if (!class_exists($className)) {
                $issues[] = "[INLINE] Class not found: '\\$className' (in " . basename($file) . ")";
                $inlineErrors++;
            }
        }
    }
}

echo "   Inline errors: $inlineErrors" . PHP_EOL;

// ================================================================
// RESULTS
// ================================================================
echo PHP_EOL;
echo "╔══════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║                    AUDIT RESULTS                     ║" . PHP_EOL;
echo "╚══════════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;

if (empty($issues)) {
    echo "✅ ZERO ISSUES FOUND! System is clean." . PHP_EOL;
} else {
    echo "❌ ISSUES FOUND: " . count($issues) . PHP_EOL . PHP_EOL;
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". $issue" . PHP_EOL;
    }
}

if (!empty($warnings)) {
    echo PHP_EOL . "⚠️  WARNINGS: " . count($warnings) . PHP_EOL;
    foreach ($warnings as $i => $w) {
        echo "  " . ($i + 1) . ". $w" . PHP_EOL;
    }
}

echo PHP_EOL . "═══════════════════════════════════════════════════════" . PHP_EOL;
echo "Total: " . count($issues) . " issues, " . count($warnings) . " warnings" . PHP_EOL;
