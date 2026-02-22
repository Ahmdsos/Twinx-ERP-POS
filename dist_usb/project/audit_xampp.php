<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  XAMPP Compatibility Deep Audit
 *  Finds ALL issues that cause errors on XAMPP but NOT on
 *  php artisan serve (SQLite vs MySQL, subfolder URLs, etc.)
 * ═══════════════════════════════════════════════════════════════
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     XAMPP Compatibility Deep Audit                       ║\n";
echo "║     Scanning for SQLite→MySQL & subfolder URL issues     ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$projectDir = __DIR__;
$issues = [];
$warnings = [];

// ═══════════════════════════════════════════════════
// AUDIT 1: Hardcoded URLs in Blade Templates
// ═══════════════════════════════════════════════════
echo "[1/7] Scanning Blade templates for hardcoded URLs...\n";

$bladeFiles = [];
$dirs = [
    $projectDir . '/resources/views',
    $projectDir . '/Modules',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir))
        continue;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isDir())
            continue;
        if (str_ends_with($file->getPathname(), '.blade.php')) {
            $bladeFiles[] = $file->getPathname();
        }
    }
}

$urlPatterns = [
    // JS string with hardcoded /path (not using route() or url())
    "/(?<![{])(?<![a-zA-Z])(['\"`])\/(?:pos|sales|inventory|accounting|hr|purchasing|admin|api|customers|deliveries|quotations|sales-invoices|sales-orders|sales-returns|customer-payments|couriers|loyalty|mission)[\/]/",
    // fetch() with hardcoded path
    "/fetch\s*\(\s*['\"]\/[a-z]/",
    // window.location with hardcoded path (not route/url)
    "/window\.location\s*(?:\.href\s*)?=\s*['\"]\/[a-z]/",
    // window.open with hardcoded path
    "/window\.open\s*\(\s*['\"]\/[a-z]/",
];

foreach ($bladeFiles as $bladeFile) {
    $content = file_get_contents($bladeFile);
    $lines = explode("\n", $content);
    $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $bladeFile));

    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);
        // Skip comments and empty lines
        if (empty($trimmed) || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '{{--'))
            continue;

        // Skip lines that already use route() or url() 
        // But still check for mixed usage (e.g. hardcoded + route on same line)

        foreach ($urlPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                // Exclude false positives: lines with {{ route() }} or {{ url() }}
                if (preg_match('/\{\{.*(?:route|url)\s*\(/', $line))
                    continue;
                // Exclude CDN/external URLs
                if (preg_match('/https?:\/\//', $line))
                    continue;
                // Exclude blade directives
                if (preg_match('/@(extends|include|section|yield)/', $line))
                    continue;

                $lineDisplay = ($lineNum + 1);
                $issues[] = [
                    'type' => 'HARDCODED_URL',
                    'severity' => 'ERROR',
                    'file' => $relativePath,
                    'line' => $lineDisplay,
                    'detail' => trim($line),
                    'fix' => "Replace hardcoded path with {{ route('name') }} or {{ url('/path') }}"
                ];
            }
        }
    }
}

echo "   Scanned " . count($bladeFiles) . " Blade templates\n";

// ═══════════════════════════════════════════════════
// AUDIT 2: Seeders not using firstOrCreate
// ═══════════════════════════════════════════════════
echo "\n[2/7] Checking seeders for idempotency...\n";

$seederDir = $projectDir . '/database/seeders';
if (is_dir($seederDir)) {
    $seederFiles = glob($seederDir . '/*.php');
    foreach ($seederFiles as $seederFile) {
        $content = file_get_contents($seederFile);
        $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $seederFile));
        $lines = explode("\n", $content);

        foreach ($lines as $lineNum => $line) {
            // Check for ::create( but NOT ::firstOrCreate or ::updateOrCreate
            if (
                preg_match('/[A-Z]\w+::create\s*\(/', $line) &&
                !preg_match('/firstOrCreate|updateOrCreate/', $line)
            ) {
                $issues[] = [
                    'type' => 'SEEDER_NOT_IDEMPOTENT',
                    'severity' => 'ERROR',
                    'file' => $relativePath,
                    'line' => $lineNum + 1,
                    'detail' => trim($line),
                    'fix' => 'Use ::firstOrCreate() or ::updateOrCreate() to avoid duplicate entry errors on re-run'
                ];
            }
        }
    }
}

echo "   Scanned " . count($seederFiles ?? []) . " seeder files\n";

// ═══════════════════════════════════════════════════
// AUDIT 3: MySQL Strict Mode
// ═══════════════════════════════════════════════════
echo "\n[3/7] Checking MySQL strict mode...\n";

$dbConfig = $projectDir . '/config/database.php';
if (file_exists($dbConfig)) {
    $content = file_get_contents($dbConfig);
    if (preg_match("/'strict'\s*=>\s*true/", $content)) {
        $issues[] = [
            'type' => 'MYSQL_STRICT_MODE',
            'severity' => 'ERROR',
            'file' => 'config/database.php',
            'line' => '-',
            'detail' => "'strict' => true (will reject NULL values that SQLite accepts)",
            'fix' => "Change to 'strict' => false for SQLite-developed codebases"
        ];
    } else {
        echo "   ✓ MySQL strict mode is disabled\n";
    }
}

// ═══════════════════════════════════════════════════
// AUDIT 4: SQLite-specific SQL in code
// ═══════════════════════════════════════════════════
echo "\n[4/7] Checking for SQLite-specific SQL...\n";

$phpFiles = [];
$codeDirs = [
    $projectDir . '/app',
    $projectDir . '/Modules',
];

foreach ($codeDirs as $dir) {
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

$sqlitePatterns = [
    '/\bIFNULL\s*\(/i' => 'IFNULL() — use COALESCE() for MySQL compatibility',
    '/\bGROUP_CONCAT\s*\(/i' => 'GROUP_CONCAT() — syntax differs between SQLite and MySQL',
    '/\bRANDOM\s*\(\)/i' => 'RANDOM() — MySQL uses RAND()',
    '/\bSQLITE_/i' => 'SQLite-specific constant or function',
    '/datetime\s*\(\s*[\'"]now[\'"]\s*\)/i' => "datetime('now') — MySQL uses NOW()",
    '/\bstrftime\s*\(/i' => 'strftime() — not available in MySQL, use DATE_FORMAT()',
    '/\bjulianday\s*\(/i' => 'julianday() — SQLite-only function',
];

foreach ($phpFiles as $phpFile) {
    $content = file_get_contents($phpFile);
    $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $phpFile));
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        // Skip comments
        if (preg_match('/^\s*(\/\/|\/\*|\*)/', $line))
            continue;

        foreach ($sqlitePatterns as $pattern => $desc) {
            if (preg_match($pattern, $line)) {
                $warnings[] = [
                    'type' => 'SQLITE_SPECIFIC_SQL',
                    'severity' => 'WARNING',
                    'file' => $relativePath,
                    'line' => $lineNum + 1,
                    'detail' => $desc,
                    'fix' => 'Use DB-agnostic syntax or Laravel query builder'
                ];
            }
        }
    }
}

echo "   Scanned " . count($phpFiles) . " PHP files\n";

// ═══════════════════════════════════════════════════
// AUDIT 5: Missing .env.xampp.example
// ═══════════════════════════════════════════════════
echo "\n[5/7] Checking XAMPP environment files...\n";

if (!file_exists($projectDir . '/.env.xampp.example')) {
    $issues[] = [
        'type' => 'MISSING_ENV',
        'severity' => 'ERROR',
        'file' => '.env.xampp.example',
        'line' => '-',
        'detail' => '.env.xampp.example is missing — SETUP.bat cannot configure MySQL',
        'fix' => 'Create .env.xampp.example with DB_CONNECTION=mysql settings'
    ];
} else {
    $envContent = file_get_contents($projectDir . '/.env.xampp.example');
    if (!str_contains($envContent, 'DB_CONNECTION=mysql')) {
        $issues[] = [
            'type' => 'WRONG_ENV',
            'severity' => 'ERROR',
            'file' => '.env.xampp.example',
            'line' => '-',
            'detail' => '.env.xampp.example does not have DB_CONNECTION=mysql',
            'fix' => 'Set DB_CONNECTION=mysql in .env.xampp.example'
        ];
    } else {
        echo "   ✓ .env.xampp.example exists with MySQL config\n";
    }
}

// ═══════════════════════════════════════════════════
// AUDIT 6: Hardcoded URLs in JS files (not Blade)
// ═══════════════════════════════════════════════════
echo "\n[6/7] Checking JS files for hardcoded API URLs...\n";

$jsFiles = [];
$jsDirs = [
    $projectDir . '/resources/js',
    $projectDir . '/public/js',
];

foreach ($jsDirs as $dir) {
    if (!is_dir($dir))
        continue;
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($rii as $file) {
        if ($file->isDir())
            continue;
        if (str_ends_with($file->getPathname(), '.js')) {
            $jsFiles[] = $file->getPathname();
        }
    }
}

$jsUrlPatterns = [
    "/fetch\s*\(\s*['\"]\/[a-z]/",
    "/axios\.\w+\s*\(\s*['\"]\/[a-z]/",
    "/\$\.(?:ajax|get|post)\s*\(\s*['\"]\/[a-z]/",
    "/window\.location\s*=\s*['\"]\/[a-z]/",
];

foreach ($jsFiles as $jsFile) {
    $content = file_get_contents($jsFile);
    $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $jsFile));
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        foreach ($jsUrlPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                $issues[] = [
                    'type' => 'JS_HARDCODED_URL',
                    'severity' => 'ERROR',
                    'file' => $relativePath,
                    'line' => $lineNum + 1,
                    'detail' => trim(substr($line, 0, 120)),
                    'fix' => 'Use a base URL variable or pass URL from Blade via data attribute'
                ];
            }
        }
    }
}

echo "   Scanned " . count($jsFiles) . " JS files\n";

// ═══════════════════════════════════════════════════
// AUDIT 7: Check other views with AJAX
// ═══════════════════════════════════════════════════
echo "\n[7/7] Checking ALL Blade views for AJAX calls with hardcoded URLs...\n";

$viewsWithAjax = [];
foreach ($bladeFiles as $bladeFile) {
    $content = file_get_contents($bladeFile);
    $relativePath = str_replace($projectDir . '/', '', str_replace('\\', '/', $bladeFile));

    // Does this view have AJAX calls?
    if (preg_match('/axios\.|fetch\s*\(|\$\.ajax|\$\.get|\$\.post/', $content)) {
        $lines = explode("\n", $content);
        $ajaxCount = 0;
        $hardcodedCount = 0;

        foreach ($lines as $lineNum => $line) {
            if (preg_match('/axios\.\w+\s*\(|fetch\s*\(|\$\.ajax|\$\.get\(|\$\.post\(/', $line)) {
                $ajaxCount++;

                // Check if it uses hardcoded path (not route/url)
                if (preg_match("/['\"]\/[a-z]/", $line) && !preg_match('/\{\{.*(?:route|url)\s*\(/', $line)) {
                    $hardcodedCount++;

                    // Skip if already found by audit 1
                    $alreadyFound = false;
                    foreach ($issues as $issue) {
                        if ($issue['file'] === $relativePath && $issue['line'] === ($lineNum + 1)) {
                            $alreadyFound = true;
                            break;
                        }
                    }

                    if (!$alreadyFound) {
                        $issues[] = [
                            'type' => 'BLADE_AJAX_HARDCODED',
                            'severity' => 'ERROR',
                            'file' => $relativePath,
                            'line' => $lineNum + 1,
                            'detail' => trim(substr($line, 0, 120)),
                            'fix' => "Replace hardcoded path with {{ route('name') }} or {{ url('/path') }}"
                        ];
                    }
                }
            }
        }

        $status = $hardcodedCount > 0 ? "❌ {$hardcodedCount} hardcoded" : "✓ OK";
        $viewsWithAjax[] = "   {$status} — {$relativePath} ({$ajaxCount} AJAX calls)";
    }
}

foreach ($viewsWithAjax as $v)
    echo $v . "\n";

// ═══════════════════════════════════════════════════
// REPORT
// ═══════════════════════════════════════════════════
echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    AUDIT RESULTS                         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

if (count($issues) === 0 && count($warnings) === 0) {
    echo "  ✅ ZERO ISSUES FOUND! System is XAMPP-compatible.\n\n";
} else {
    // Errors
    if (count($issues) > 0) {
        echo "  ❌ ERRORS: " . count($issues) . " (will cause failures on XAMPP)\n";
        echo "  ─────────────────────────────────────────────\n";
        foreach ($issues as $i => $issue) {
            echo "  " . ($i + 1) . ". [{$issue['type']}] {$issue['file']}:{$issue['line']}\n";
            echo "     Detail: {$issue['detail']}\n";
            echo "     Fix: {$issue['fix']}\n\n";
        }
    }

    // Warnings
    if (count($warnings) > 0) {
        echo "  ⚠️  WARNINGS: " . count($warnings) . " (may cause issues)\n";
        echo "  ─────────────────────────────────────────────\n";
        foreach ($warnings as $i => $w) {
            echo "  " . ($i + 1) . ". [{$w['type']}] {$w['file']}:{$w['line']}\n";
            echo "     Detail: {$w['detail']}\n\n";
        }
    }
}

echo "\n  Total: " . count($issues) . " errors, " . count($warnings) . " warnings\n\n";
