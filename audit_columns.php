<?php
/**
 * Column Mismatch Audit Script
 * Scans all PHP files for SQL column references and checks them against migration definitions.
 * Run: php audit_columns.php
 */

echo "\n╔══════════════════════════════════════════════╗\n";
echo "║  Column Mismatch Audit - Full System Scan   ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

$baseDir = __DIR__;

// ================================================================
// STEP 1: Build column map from migrations
// ================================================================
$tableColumns = [];

// Scan all migration files
$migrationDirs = glob($baseDir . '/Modules/*/database/migrations', GLOB_ONLYDIR);
$migrationDirs[] = $baseDir . '/database/migrations';

foreach ($migrationDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);

        // Find table name from Schema::create
        if (preg_match_all("/Schema::create\s*\(\s*'(\w+)'/", $content, $tableMatches)) {
            foreach ($tableMatches[1] as $tableName) {
                if (!isset($tableColumns[$tableName])) {
                    $tableColumns[$tableName] = ['id', 'created_at', 'updated_at']; // defaults
                }

                // Find all column definitions
                if (preg_match_all("/\\\$table->(?:string|integer|bigInteger|unsignedBigInteger|decimal|float|boolean|text|longText|date|dateTime|timestamp|enum|foreignId|json)\s*\(\s*'(\w+)'/", $content, $colMatches)) {
                    foreach ($colMatches[1] as $col) {
                        $colName = str_replace('_id', '', $col); // foreignId adds _id
                        if (strpos($col, '_id') !== false || !in_array($col, $tableColumns[$tableName])) {
                            $tableColumns[$tableName][] = $col;
                        }
                    }
                }

                // softDeletes
                if (strpos($content, 'softDeletes') !== false) {
                    $tableColumns[$tableName][] = 'deleted_at';
                }

                // timestamps
                if (strpos($content, 'timestamps') !== false) {
                    // already added
                }

                // rememberToken
                if (strpos($content, 'rememberToken') !== false) {
                    $tableColumns[$tableName][] = 'remember_token';
                }
            }
        }
    }
}

// Manual model->table mappings for Eloquent queries
$modelToTable = [
    'Product' => 'products',
    'Category' => 'categories',
    'Unit' => 'units',
    'Warehouse' => 'warehouses',
    'ProductStock' => 'product_stock',
    'StockMovement' => 'stock_movements',
    'Customer' => 'customers',
    'SalesOrder' => 'sales_orders',
    'SalesInvoice' => 'sales_invoices',
    'SalesReturn' => 'sales_returns',
    'DeliveryOrder' => 'delivery_orders',
    'Quotation' => 'quotations',
    'CustomerPayment' => 'customer_payments',
    'Supplier' => 'suppliers',
    'PurchaseOrder' => 'purchase_orders',
    'PurchaseInvoice' => 'purchase_invoices',
    'Employee' => 'hr_employees',
    'Attendance' => 'hr_attendance',
    'Leave' => 'hr_leaves',
    'DeliveryDriver' => 'hr_delivery_drivers',
    'Advance' => 'hr_advances',
    'Payroll' => 'hr_payrolls',
    'Account' => 'accounts',
    'JournalEntry' => 'journal_entries',
    'FiscalYear' => 'fiscal_years',
    'ExpenseCategory' => 'expense_categories',
    'Expense' => 'expenses',
    'Setting' => 'settings',
    'User' => 'users',
    'PosShift' => 'pos_shifts',
];

echo "📋 Found " . count($tableColumns) . " tables in migrations\n\n";

// ================================================================
// STEP 2: Scan all PHP files for column references
// ================================================================
$issues = [];
$scannedFiles = 0;

$phpFiles = [];
foreach (['Modules/*/Http/Controllers/*.php', 'Modules/*/Services/*.php', 'Modules/*/Models/*.php', 'app/Http/Controllers/*.php'] as $pattern) {
    $phpFiles = array_merge($phpFiles, glob($baseDir . '/' . $pattern));
}
// Also scan subdirectories
foreach (['Modules/*/Http/Controllers/**/*.php'] as $pattern) {
    $phpFiles = array_merge($phpFiles, glob($baseDir . '/' . $pattern));
}

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $relPath = str_replace($baseDir . '/', '', $file);
    $scannedFiles++;
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        $lineNumber = $lineNum + 1;

        // Pattern 1: ->select('col1', 'col2', ...) or ->select(['col1', 'col2'])
        if (preg_match("/->select\s*\(\s*['\[]/", $line)) {
            if (preg_match_all("/'(\w+)'/", $line, $cols)) {
                // Try to determine which model/table this refers to
                // Look for Model::select or $query from context
                foreach ($cols[1] as $col) {
                    if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at', 'as', 'desc', 'asc']))
                        continue;
                    // Check against known tables
                    foreach ($modelToTable as $model => $table) {
                        if (strpos($line, $model . '::') !== false && isset($tableColumns[$table])) {
                            if (!in_array($col, $tableColumns[$table])) {
                                $issues[] = [
                                    'file' => $relPath,
                                    'line' => $lineNumber,
                                    'type' => 'SELECT',
                                    'column' => $col,
                                    'table' => $table,
                                    'context' => trim($line),
                                ];
                            }
                        }
                    }
                }
            }
        }

        // Pattern 2: orderBy('column')
        if (preg_match("/orderBy\s*\(\s*'(\w+)'/", $line, $match)) {
            $col = $match[1];
            if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']))
                continue;

            foreach ($modelToTable as $model => $table) {
                if ((strpos($line, $model . '::') !== false || strpos($content, "use.*$model") !== false) && isset($tableColumns[$table])) {
                    if (!in_array($col, $tableColumns[$table])) {
                        $issues[] = [
                            'file' => $relPath,
                            'line' => $lineNumber,
                            'type' => 'ORDER BY',
                            'column' => $col,
                            'table' => $table,
                            'context' => trim($line),
                        ];
                    }
                }
            }
        }

        // Pattern 3: where('column', ...) - only direct model calls
        if (preg_match("/->where\s*\(\s*'(\w+)'/", $line, $match)) {
            $col = $match[1];
            if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at', 'key', 'status', 'type']))
                continue;

            foreach ($modelToTable as $model => $table) {
                if (strpos($line, $model . '::') !== false && isset($tableColumns[$table])) {
                    if (!in_array($col, $tableColumns[$table])) {
                        $issues[] = [
                            'file' => $relPath,
                            'line' => $lineNumber,
                            'type' => 'WHERE',
                            'column' => $col,
                            'table' => $table,
                            'context' => trim($line),
                        ];
                    }
                }
            }
        }
    }
}

echo "🔍 Scanned $scannedFiles PHP files\n\n";

// ================================================================
// STEP 3: Known problematic patterns (manual checks)
// ================================================================

// Check for 'code' references on products (should be 'sku')
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $relPath = str_replace($baseDir . '/', '', $file);
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        // Products.code (should be sku)
        if (preg_match("/Product.*['\"]code['\"]/i", $line) && !preg_match("/employee_code|account.*code|barcode/i", $line)) {
            $issues[] = [
                'file' => $relPath,
                'line' => $lineNum + 1,
                'type' => 'COLUMN',
                'column' => 'code (should be sku)',
                'table' => 'products',
                'context' => trim($line),
            ];
        }

        // Employee full_name in SQL context (not accessor)
        if (preg_match("/->(?:where|orderBy|select)\s*\(\s*['\"]full_name['\"]/", $line) && strpos($relPath, 'HR') !== false) {
            $issues[] = [
                'file' => $relPath,
                'line' => $lineNum + 1,
                'type' => 'ACCESSOR_IN_SQL',
                'column' => 'full_name (accessor, not column - use first_name)',
                'table' => 'hr_employees',
                'context' => trim($line),
            ];
        }

        // sale_price vs selling_price
        if (preg_match("/['\"]sale_price['\"]/", $line) && strpos($line, 'wholesale') === false) {
            $issues[] = [
                'file' => $relPath,
                'line' => $lineNum + 1,
                'type' => 'COLUMN',
                'column' => 'sale_price (should be selling_price)',
                'table' => 'products',
                'context' => trim($line),
            ];
        }
    }
}

// Also scan blade templates for SQL-like patterns
$bladeFiles = array_merge(
    glob($baseDir . '/resources/views/**/*.blade.php'),
    glob($baseDir . '/resources/views/**/**/*.blade.php'),
    glob($baseDir . '/Modules/*/resources/views/**/*.blade.php'),
    glob($baseDir . '/Modules/*/resources/views/**/**/*.blade.php')
);

// ================================================================
// STEP 4: Report
// ================================================================
// Deduplicate
$seen = [];
$uniqueIssues = [];
foreach ($issues as $issue) {
    $key = $issue['file'] . ':' . $issue['line'] . ':' . $issue['column'];
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $uniqueIssues[] = $issue;
    }
}

if (empty($uniqueIssues)) {
    echo "✅ No column mismatches found!\n";
} else {
    echo "🔴 Found " . count($uniqueIssues) . " potential column mismatches:\n\n";
    echo str_pad('Type', 18) . str_pad('Table', 22) . str_pad('Column', 35) . "File:Line\n";
    echo str_repeat('─', 120) . "\n";

    foreach ($uniqueIssues as $issue) {
        echo str_pad($issue['type'], 18)
            . str_pad($issue['table'], 22)
            . str_pad($issue['column'], 35)
            . $issue['file'] . ':' . $issue['line'] . "\n";
    }

    echo "\n📝 Context Details:\n";
    echo str_repeat('─', 80) . "\n";
    foreach ($uniqueIssues as $i => $issue) {
        echo ($i + 1) . ". [{$issue['file']}:{$issue['line']}]\n";
        echo "   " . substr($issue['context'], 0, 120) . "\n\n";
    }
}

echo "\n✅ Audit complete.\n";
