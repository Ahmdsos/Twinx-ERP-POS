@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

REM ═══════════════════════════════════════════════════════════
REM  Twinx ERP — Safe Cleanup
REM  Moves junk/debug scripts to _backup_before_cleanup/
REM  Clears caches and logs.
REM  Idempotent — safe to re-run.
REM ═══════════════════════════════════════════════════════════

set "SCRIPT_DIR=%~dp0"
set "REPO_DIR=%SCRIPT_DIR%.."
set "BACKUP_DIR=%REPO_DIR%\_backup_before_cleanup"

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║     Twinx ERP — Safe Cleanup                        ║
echo ╚══════════════════════════════════════════════════════╝
echo.

REM ─── Step 1: Create backup directory ───
echo [1/5] Creating backup directory...
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"
echo   ✓ %BACKUP_DIR%

REM ─── Step 2: Move root-level debug/test PHP scripts ───
echo.
echo [2/5] Moving debug scripts to backup...
set "JUNK_PHP=audit_accounts.php check_columns.php check_db_balance.php check_truth.php check_user.php debug_stock.php dump_invoice.php recalc_verify.php test-system.php test_full_erp_cycle.php test_pos_e2e.php verify_integrity.php verify_inventory_write_path.php verify_phase3_fix.php verify_pos_hardening.php verify_precision.php verify_shift_lifecycle.php"

set "MOVED=0"
for %%f in (%JUNK_PHP%) do (
    if exist "%REPO_DIR%\%%f" (
        move "%REPO_DIR%\%%f" "%BACKUP_DIR%\%%f" >nul 2>&1
        echo   → %%f
        set /a MOVED+=1
    )
)

REM ─── Step 3: Move root-level one-off Node scripts ───
echo.
echo [3/5] Moving one-off Node scripts to backup...
set "JUNK_JS=add_account_translations.cjs batch_fix_blade.cjs batch_fix_blade.js fix_theme_regressions.js update_translations.js"

for %%f in (%JUNK_JS%) do (
    if exist "%REPO_DIR%\%%f" (
        move "%REPO_DIR%\%%f" "%BACKUP_DIR%\%%f" >nul 2>&1
        echo   → %%f
        set /a MOVED+=1
    )
)
echo   Total moved: %MOVED% files

REM ─── Step 4: Delete NativePHP SQLite files ───
echo.
echo [4/5] Cleaning NativePHP and cache files...
if exist "%REPO_DIR%\database\nativephp.sqlite" (
    move "%REPO_DIR%\database\nativephp.sqlite" "%BACKUP_DIR%\nativephp.sqlite" >nul 2>&1
    echo   → nativephp.sqlite
)
if exist "%REPO_DIR%\database\nativephp.sqlite-shm" del "%REPO_DIR%\database\nativephp.sqlite-shm" >nul 2>&1
if exist "%REPO_DIR%\database\nativephp.sqlite-wal" del "%REPO_DIR%\database\nativephp.sqlite-wal" >nul 2>&1
if exist "%REPO_DIR%\.phpunit.result.cache" del "%REPO_DIR%\.phpunit.result.cache" >nul 2>&1
echo   ✓ NativePHP + cache cleaned

REM ─── Step 5: Clear Laravel caches and logs ───
echo.
echo [5/5] Clearing Laravel logs and caches...

REM Clear log (but keep file)
if exist "%REPO_DIR%\storage\logs\laravel.log" (
    type nul > "%REPO_DIR%\storage\logs\laravel.log"
    echo   ✓ laravel.log cleared
)

REM Clear framework caches
if exist "%REPO_DIR%\storage\framework\cache\data" (
    for /d %%d in ("%REPO_DIR%\storage\framework\cache\data\*") do rmdir /s /q "%%d" 2>nul
    del /q "%REPO_DIR%\storage\framework\cache\data\*" 2>nul
    echo   ✓ Cache data cleared
)

if exist "%REPO_DIR%\storage\framework\views" (
    del /q "%REPO_DIR%\storage\framework\views\*.php" 2>nul
    echo   ✓ Compiled views cleared
)

if exist "%REPO_DIR%\storage\framework\sessions" (
    del /q "%REPO_DIR%\storage\framework\sessions\*" 2>nul
    echo   ✓ Sessions cleared
)

REM Clear demo directory
if exist "%REPO_DIR%\storage\demo" (
    rmdir /s /q "%REPO_DIR%\storage\demo" 2>nul
    echo   ✓ storage/demo removed
)

REM Clear empty reports directory
if exist "%REPO_DIR%\reports" (
    rmdir /q "%REPO_DIR%\reports" 2>nul
    echo   ✓ Empty reports/ removed
)

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║        ✓ Cleanup Complete!                          ║
echo ╠══════════════════════════════════════════════════════╣
echo ║  Backup: _backup_before_cleanup\                    ║
echo ║  Moved:  %MOVED% debug/test scripts                   ║
echo ║  Cleared: logs, cache, sessions, compiled views     ║
echo ║                                                      ║
echo ║  Your .env and runtime config are UNTOUCHED.        ║
echo ╚══════════════════════════════════════════════════════╝
echo.

exit /b 0
