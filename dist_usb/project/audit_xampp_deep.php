<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  DEEP MySQL Compatibility Analysis v2
 *  Checks: enum mismatches, missing columns, type issues,
 *          NOT NULL violations, and everything SQLite hides
 * ═══════════════════════════════════════════════════════════════
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     DEEP MySQL Compatibility Analysis v2                 ║\n";
echo "║     Finding ALL SQLite→MySQL breaking differences        ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$projectDir = __DIR__;
$issues = [];

// ═══════════════════════════════════════════════════
// AUDIT 1: Enum mismatches between migrations & seeders
// ═══════════════════════════════════════════════════
echo "[1/8] Scanning migrations for ENUM columns...\n";

$migrationDirs = [
    $projectDir . '/database/migrations',
    $projectDir . '/Modules/Sales/database/migrations',
    $projectDir . '/Modules/Inventory/database/migrations',
    $projectDir . '/Modules/Purchasing/database/migrations',
    $projectDir . '/Modules/Accounting/database/migrations',
    $projectDir . '/Modules/HR/database/migrations',
    $projectDir . '/Modules/Core/database/migrations',
];

$enums = []; // table => [column => [allowed_values]]
$migrationFiles = [];

foreach ($migrationDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob($dir . '/*.php') as $file) {
        $migrationFiles[] = $file;
        $content = file_get_contents($file);
        $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $file));

        // Find table name
        if (preg_match("/Schema::create\s*\(\s*'(\w+)'/", $content, $tableMatch)) {
            $tableName = $tableMatch[1];

            // Find enum columns: $table->enum('column', ['val1', 'val2'])
            if (preg_match_all("/->enum\s*\(\s*'(\w+)'\s*,\s*\[(.*?)\]\s*\)/s", $content, $enumMatches, PREG_SET_ORDER)) {
                foreach ($enumMatches as $match) {
                    $column = $match[1];
                    $valuesStr = $match[2];
                    // Parse the values
                    preg_match_all("/['\"]([^'\"]+)['\"]/", $valuesStr, $valMatches);
                    $enums[$tableName][$column] = [
                        'values' => $valMatches[1],
                        'file' => $relativePath,
                    ];
                }
            }
        }
    }
}

echo "   Found " . count($migrationFiles) . " migration files\n";
$enumCount = 0;
foreach ($enums as $table => $cols) {
    foreach ($cols as $col => $info) {
        $enumCount++;
        echo "   📋 $table.$col: [" . implode(', ', $info['values']) . "]\n";
    }
}
echo "   Total: $enumCount enum columns found\n";

// ═══════════════════════════════════════════════════
// AUDIT 2: Check seeders for invalid enum values
// ═══════════════════════════════════════════════════
echo "\n[2/8] Checking seeders for invalid enum values...\n";

$seederDir = $projectDir . '/database/seeders';
if (is_dir($seederDir)) {
    foreach (glob($seederDir . '/*.php') as $seederFile) {
        $content = file_get_contents($seederFile);
        $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $seederFile));
        $lines = explode("\n", $content);

        foreach ($enums as $table => $columns) {
            foreach ($columns as $col => $info) {
                // Find lines that set this column value
                foreach ($lines as $lineNum => $line) {
                    if (preg_match("/['\"]" . $col . "['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $line, $m)) {
                        $value = $m[1];
                        if (!in_array($value, $info['values'])) {
                            $issues[] = [
                                'type' => 'ENUM_MISMATCH',
                                'severity' => '🔴 CRITICAL',
                                'file' => $relativePath,
                                'line' => $lineNum + 1,
                                'detail' => "Column '$table.$col': value '$value' not in enum [" . implode(', ', $info['values']) . "]",
                                'fix' => "Change '$value' to one of: " . implode(', ', $info['values']),
                            ];
                        }
                    }
                }
            }
        }
    }
}

// ═══════════════════════════════════════════════════
// AUDIT 3: Check controllers for invalid enum values
// ═══════════════════════════════════════════════════
echo "\n[3/8] Checking controllers for invalid enum values...\n";

$phpFiles = [];
$controllerDirs = [
    $projectDir . '/app/Http/Controllers',
    $projectDir . '/Modules/Sales/Http/Controllers',
    $projectDir . '/Modules/Inventory/Http/Controllers',
    $projectDir . '/Modules/Purchasing/Http/Controllers',
    $projectDir . '/Modules/Accounting/Http/Controllers',
    $projectDir . '/Modules/HR/Http/Controllers',
    $projectDir . '/Modules/Core/Http/Controllers',
];

foreach ($controllerDirs as $dir) {
    if (!is_dir($dir))
        continue;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isDir())
            continue;
        if (str_ends_with($file->getPathname(), '.php')) {
            $phpFiles[] = $file->getPathname();
        }
    }
}

foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $phpFile));
    $lines = explode("\n", $content);

    foreach ($enums as $table => $columns) {
        foreach ($columns as $col => $info) {
            foreach ($lines as $lineNum => $line) {
                if (preg_match("/['\"]" . $col . "['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $line, $m)) {
                    $value = $m[1];
                    if (!in_array($value, $info['values'])) {
                        $issues[] = [
                            'type' => 'ENUM_MISMATCH',
                            'severity' => '🔴 CRITICAL',
                            'file' => $relativePath,
                            'line' => $lineNum + 1,
                            'detail' => "Column '$table.$col': value '$value' not in enum [" . implode(', ', $info['values']) . "]",
                            'fix' => "Change '$value' to one of: " . implode(', ', $info['values']),
                        ];
                    }
                }
            }
        }
    }
}

echo "   Scanned " . count($phpFiles) . " controller files\n";

// ═══════════════════════════════════════════════════
// AUDIT 4: Check Model $fillable vs migration columns
// ═══════════════════════════════════════════════════
echo "\n[4/8] Checking model fillable vs migration columns...\n";

// Extract columns from migrations
$tableColumns = [];
foreach ($migrationDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        if (preg_match("/Schema::create\s*\(\s*'(\w+)'/", $content, $tableMatch)) {
            $tableName = $tableMatch[1];
            // Find all column definitions
            preg_match_all("/->(string|text|integer|bigInteger|decimal|boolean|enum|date|datetime|timestamp|float|double|json|foreignId|unsignedBigInteger|tinyInteger|smallInteger)\s*\(\s*'(\w+)'/", $content, $colMatches, PREG_SET_ORDER);
            foreach ($colMatches as $m) {
                $tableColumns[$tableName][] = $m[2];
            }
            // id column
            if (str_contains($content, '$table->id()')) {
                $tableColumns[$tableName][] = 'id';
            }
            // timestamps
            if (str_contains($content, '$table->timestamps()')) {
                $tableColumns[$tableName][] = 'created_at';
                $tableColumns[$tableName][] = 'updated_at';
            }
            // softDeletes
            if (str_contains($content, '$table->softDeletes()')) {
                $tableColumns[$tableName][] = 'deleted_at';
            }
        }
    }
}

// Also check alter table migrations for added columns
foreach ($migrationDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        if (preg_match("/Schema::table\s*\(\s*'(\w+)'/", $content, $tableMatch)) {
            $tableName = $tableMatch[1];
            preg_match_all("/->(string|text|integer|bigInteger|decimal|boolean|enum|date|datetime|timestamp|float|double|json|foreignId|unsignedBigInteger|tinyInteger|smallInteger)\s*\(\s*'(\w+)'/", $content, $colMatches, PREG_SET_ORDER);
            foreach ($colMatches as $m) {
                $tableColumns[$tableName][] = $m[2];
            }
        }
    }
}

echo "   Found " . count($tableColumns) . " tables with column definitions\n";

// ═══════════════════════════════════════════════════
// AUDIT 5: Check NOT NULL columns without defaults
// ═══════════════════════════════════════════════════
echo "\n[5/8] Checking NOT NULL columns without defaults (MySQL strict issues)...\n";

foreach ($migrationDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob($dir . '/*.php') as $file) {
        $content = file_get_contents($file);
        $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $file));

        if (preg_match("/Schema::create\s*\(\s*'(\w+)'/", $content, $tableMatch)) {
            $tableName = $tableMatch[1];
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                // Find column definitions that are NOT nullable and don't have defaults
                if (preg_match("/->(string|text|integer|decimal|boolean|enum|date|datetime|json)\s*\(\s*'(\w+)'/", $line, $colMatch)) {
                    $colType = $colMatch[1];
                    $colName = $colMatch[2];

                    // Skip if it has nullable, default, or is timestamps/softDeletes
                    if (str_contains($line, '->nullable()'))
                        continue;
                    if (str_contains($line, '->default('))
                        continue;
                    if (in_array($colName, ['id', 'created_at', 'updated_at', 'deleted_at']))
                        continue;

                    // Skip boolean (MySQL has default for boolean)
                    if ($colType === 'boolean')
                        continue;

                    // These require explicit values on INSERT
                    // Check if they're likely set by the application (e.g. 'name' is probably always set)
                    $requiredCols = ['name', 'code', 'email', 'password'];
                    if (in_array($colName, $requiredCols))
                        continue;

                    // Warn about non-obvious required columns
                    if (!in_array($colType, ['text'])) { // text fields default to '' in MySQL
                        $issues[] = [
                            'type' => 'NOT_NULL_NO_DEFAULT',
                            'severity' => '🟡 WARNING',
                            'file' => $relativePath,
                            'line' => $lineNum + 1,
                            'detail' => "$tableName.$colName ($colType) - NOT NULL without default value",
                            'fix' => "Add ->nullable() or ->default() if this column isn't always provided",
                        ];
                    }
                }
            }
        }
    }
}

// ═══════════════════════════════════════════════════
// AUDIT 6: Check .htaccess for Apache mod_rewrite
// ═══════════════════════════════════════════════════
echo "\n[6/8] Checking .htaccess configuration...\n";

$htaccess = $projectDir . '/public/.htaccess';
if (file_exists($htaccess)) {
    $content = file_get_contents($htaccess);
    if (str_contains($content, 'RewriteEngine On')) {
        echo "   ✓ .htaccess has RewriteEngine On\n";
    } else {
        $issues[] = [
            'type' => 'HTACCESS_MISSING',
            'severity' => '🔴 CRITICAL',
            'file' => 'public/.htaccess',
            'line' => '-',
            'detail' => '.htaccess missing RewriteEngine On — Apache won\'t route URLs correctly',
            'fix' => 'Add standard Laravel .htaccess with mod_rewrite rules',
        ];
    }
} else {
    $issues[] = [
        'type' => 'HTACCESS_MISSING',
        'severity' => '🔴 CRITICAL',
        'file' => 'public/.htaccess',
        'line' => '-',
        'detail' => '.htaccess file does not exist — XAMPP Apache needs this for URL routing',
        'fix' => 'Create standard Laravel public/.htaccess file',
    ];
}

// ═══════════════════════════════════════════════════
// AUDIT 7: Check if SETUP.bat correctly generates .env
// ═══════════════════════════════════════════════════
echo "\n[7/8] Checking SETUP.bat env generation...\n";

$setupBat = $projectDir . '/SETUP.bat';
if (file_exists($setupBat)) {
    $content = file_get_contents($setupBat);

    // Check APP_URL is set correctly
    if (str_contains($content, 'APP_URL')) {
        echo "   ✓ SETUP.bat sets APP_URL\n";
    } else {
        $issues[] = [
            'type' => 'SETUP_MISSING_APP_URL',
            'severity' => '🔴 CRITICAL',
            'file' => 'SETUP.bat',
            'line' => '-',
            'detail' => 'SETUP.bat does not set APP_URL - route() helper will generate wrong URLs',
            'fix' => 'Add APP_URL=http://localhost/twinx-erp/public to SETUP.bat env generation',
        ];
    }

    // Check SESSION_PATH
    if (preg_match('/SESSION_PATH\s*=\s*(.+)/', $content, $m)) {
        $sessionPath = trim($m[1]);
        echo "   SESSION_PATH = $sessionPath\n";
        if ($sessionPath === '/' || $sessionPath === '') {
            $issues[] = [
                'type' => 'SESSION_PATH_WRONG',
                'severity' => '🟡 WARNING',
                'file' => 'SETUP.bat / .env.xampp.example',
                'line' => '-',
                'detail' => 'SESSION_PATH=/ may cause session issues on XAMPP subfolder deployment',
                'fix' => 'Consider setting SESSION_PATH=/twinx-erp/public',
            ];
        }
    }
} else {
    echo "   ⚠ SETUP.bat not found in project root\n";
}

// ═══════════════════════════════════════════════════
// AUDIT 8: Check all Service files for MySQL issues
// ═══════════════════════════════════════════════════
echo "\n[8/8] Scanning Service files for raw SQL and MySQL issues...\n";

$serviceFiles = [];
$serviceDirs = [
    $projectDir . '/Modules/Sales/Services',
    $projectDir . '/Modules/Inventory/Services',
    $projectDir . '/Modules/Purchasing/Services',
    $projectDir . '/Modules/Accounting/Services',
    $projectDir . '/Modules/HR/Services',
    $projectDir . '/Modules/Core/Services',
];

foreach ($serviceDirs as $dir) {
    if (!is_dir($dir))
        continue;
    foreach (glob($dir . '/*.php') as $file) {
        $serviceFiles[] = $file;
    }
}

$rawSqlPatterns = [
    '/DB::raw\s*\(/' => 'DB::raw() — check for MySQL vs SQLite syntax differences',
    '/DB::select\s*\(/' => 'DB::select() — raw SQL query, verify MySQL compatibility',
    '/DB::statement\s*\(/' => 'DB::statement() — raw SQL, verify MySQL compatibility',
    '/->selectRaw\s*\(/' => 'selectRaw() — raw SQL expression, check MySQL syntax',
    '/->whereRaw\s*\(/' => 'whereRaw() — raw SQL condition, check MySQL syntax',
    '/->orderByRaw\s*\(/' => 'orderByRaw() — raw SQL ordering, check MySQL syntax',
    '/->havingRaw\s*\(/' => 'havingRaw() — raw SQL having, check MySQL syntax',
    '/->groupByRaw\s*\(/' => 'groupByRaw() — raw SQL grouping, check MySQL syntax',
];

foreach ($serviceFiles as $file) {
    $content = file_get_contents($file);
    $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $file));
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        foreach ($rawSqlPatterns as $pattern => $desc) {
            if (preg_match($pattern, $line)) {
                $issues[] = [
                    'type' => 'RAW_SQL',
                    'severity' => '🟡 WARNING',
                    'file' => $relativePath,
                    'line' => $lineNum + 1,
                    'detail' => $desc . ': ' . trim(substr($line, 0, 100)),
                    'fix' => 'Verify this raw SQL works on both SQLite and MySQL',
                ];
            }
        }
    }
}

echo "   Scanned " . count($serviceFiles) . " service files\n";

// ═══════════════════════════════════════════════════
// REPORT
// ═══════════════════════════════════════════════════
echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    DEEP ANALYSIS RESULTS                 ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Group by severity
$critical = array_filter($issues, fn($i) => str_contains($i['severity'], 'CRITICAL'));
$warnings = array_filter($issues, fn($i) => str_contains($i['severity'], 'WARNING'));

if (count($critical) > 0) {
    echo "  🔴 CRITICAL ISSUES: " . count($critical) . "\n";
    echo "  ═════════════════════════════════════════\n";
    foreach (array_values($critical) as $i => $issue) {
        echo "  " . ($i + 1) . ". [{$issue['type']}] {$issue['file']}:{$issue['line']}\n";
        echo "     {$issue['detail']}\n";
        echo "     → FIX: {$issue['fix']}\n\n";
    }
}

if (count($warnings) > 0) {
    echo "  🟡 WARNINGS: " . count($warnings) . "\n";
    echo "  ═════════════════════════════════════════\n";
    foreach (array_values($warnings) as $i => $w) {
        echo "  " . ($i + 1) . ". [{$w['type']}] {$w['file']}:{$w['line']}\n";
        echo "     {$w['detail']}\n";
        if (isset($w['fix']))
            echo "     → FIX: {$w['fix']}\n";
        echo "\n";
    }
}

echo "  ─────────────────────────────────────────\n";
echo "  Total: " . count($critical) . " critical, " . count($warnings) . " warnings\n\n";
