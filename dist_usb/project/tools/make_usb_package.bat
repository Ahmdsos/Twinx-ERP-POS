@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

REM ═══════════════════════════════════════════════════════════
REM  Twinx ERP — USB Package Builder
REM  Usage: make_usb_package.bat [MODE]
REM    MODE=A  → Offline (includes vendor + built assets)
REM    MODE=B  → Lean   (excludes vendor, requires composer/node on client)
REM  Default: MODE=A
REM
REM  Output structure:
REM    dist_usb/
REM    ├── SETUP.bat              ← Client double-clicks THIS
REM    ├── requirements/
REM    │   └── DOWNLOAD_LINKS.txt ← Download links for XAMPP etc.
REM    └── project/               ← Clean project files
REM        ├── tools/
REM        ├── docs/
REM        ├── app/, Modules/, ...
REM        └── ...
REM ═══════════════════════════════════════════════════════════

set "MODE=%~1"
if "%MODE%"=="" set "MODE=A"

set "SCRIPT_DIR=%~dp0"
set "REPO_DIR=%SCRIPT_DIR%.."
set "DIST_DIR=%REPO_DIR%\dist_usb"
set "PROJECT_DIR=%DIST_DIR%\project"
set "ZIP_FILE=%REPO_DIR%\dist_usb.zip"

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║     Twinx ERP — USB Package Builder  (MODE=%MODE%)     ║
echo ╚══════════════════════════════════════════════════════╝
echo.

REM ─── Clean previous build ───
if exist "%DIST_DIR%" (
    echo Cleaning previous build...
    rmdir /s /q "%DIST_DIR%" 2>nul
)
mkdir "%DIST_DIR%"
mkdir "%PROJECT_DIR%"

REM ─── Junk scripts to exclude ───
set "JUNK_PHP=audit_accounts.php check_columns.php check_db_balance.php check_truth.php check_user.php debug_stock.php dump_invoice.php recalc_verify.php test-system.php test_full_erp_cycle.php test_pos_e2e.php verify_integrity.php verify_inventory_write_path.php verify_phase3_fix.php verify_pos_hardening.php verify_precision.php verify_shift_lifecycle.php"
set "JUNK_JS=add_account_translations.cjs batch_fix_blade.cjs batch_fix_blade.js fix_theme_regressions.js update_translations.js"

echo [1/5] Copying project files...

if "%MODE%"=="B" (
    echo   Mode B: Lean (excluding vendor and tests)
    robocopy "%REPO_DIR%" "%PROJECT_DIR%" /MIR /XD ".git" "node_modules" ".agent" "_backup_before_cleanup" "dist_usb" "requirements" "storage\logs" vendor tests /XF *.log database.sqlite nativephp.sqlite nativephp.sqlite-shm nativephp.sqlite-wal .phpunit.result.cache .license.key phpunit.xml SETUP.bat %JUNK_PHP% %JUNK_JS% /NFL /NDL /NP /NJH /NJS >nul 2>&1
) else (
    echo   Mode A: Offline (including vendor, excluding tests)
    robocopy "%REPO_DIR%" "%PROJECT_DIR%" /MIR /XD ".git" "node_modules" ".agent" "_backup_before_cleanup" "dist_usb" "requirements" "storage\logs" tests /XF *.log database.sqlite nativephp.sqlite nativephp.sqlite-shm nativephp.sqlite-wal .phpunit.result.cache .license.key phpunit.xml SETUP.bat %JUNK_PHP% %JUNK_JS% /NFL /NDL /NP /NJH /NJS >nul 2>&1
)
echo   ✓ Project files copied

REM ─── Copy SETUP.bat to root of dist ───
echo.
echo [2/5] Adding SETUP.bat launcher...
if exist "%REPO_DIR%\SETUP.bat" (
    copy "%REPO_DIR%\SETUP.bat" "%DIST_DIR%\SETUP.bat" >nul
    echo   ✓ SETUP.bat added to package root
) else (
    echo   ⚠ SETUP.bat not found in repo root
)

REM ─── Copy requirements folder ───
echo.
echo [3/5] Adding requirements and download links...
mkdir "%DIST_DIR%\requirements" 2>nul
if exist "%REPO_DIR%\requirements\DOWNLOAD_LINKS.txt" (
    copy "%REPO_DIR%\requirements\DOWNLOAD_LINKS.txt" "%DIST_DIR%\requirements\DOWNLOAD_LINKS.txt" >nul
    echo   ✓ DOWNLOAD_LINKS.txt added
) else (
    echo   ⚠ DOWNLOAD_LINKS.txt not found
)

REM ─── Ensure empty dirs that Laravel needs ───
echo.
echo [4/5] Creating required empty directories...
if not exist "%PROJECT_DIR%\storage\app\public" mkdir "%PROJECT_DIR%\storage\app\public"
if not exist "%PROJECT_DIR%\storage\framework\cache\data" mkdir "%PROJECT_DIR%\storage\framework\cache\data"
if not exist "%PROJECT_DIR%\storage\framework\sessions" mkdir "%PROJECT_DIR%\storage\framework\sessions"
if not exist "%PROJECT_DIR%\storage\framework\views" mkdir "%PROJECT_DIR%\storage\framework\views"
if not exist "%PROJECT_DIR%\storage\logs" mkdir "%PROJECT_DIR%\storage\logs"
if not exist "%PROJECT_DIR%\bootstrap\cache" mkdir "%PROJECT_DIR%\bootstrap\cache"
echo   ✓ Directory structure ready

REM ─── Create ZIP ───
echo.
echo [5/5] Creating dist_usb.zip...
if exist "%ZIP_FILE%" del "%ZIP_FILE%"
powershell -NoProfile -Command "Compress-Archive -Path '%DIST_DIR%\*' -DestinationPath '%ZIP_FILE%' -Force"
set "ZIP_OK=0"
if exist "%ZIP_FILE%" set "ZIP_OK=1"

if "!ZIP_OK!"=="1" (
    for %%A in ("%ZIP_FILE%") do set "ZIP_SIZE=%%~zA"
    set /a "ZIP_MB=!ZIP_SIZE! / 1048576"
) else (
    echo   ✗ Failed to create ZIP
    pause
    exit /b 1
)
echo   ✓ Created: dist_usb.zip [!ZIP_MB! MB]

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║        ✓ USB Package Ready!                         ║
echo ╠══════════════════════════════════════════════════════╣
echo ║                                                      ║
echo ║  Structure:                                          ║
echo ║    dist_usb/                                         ║
echo ║    +-- SETUP.bat         (client double-clicks this) ║
echo ║    +-- requirements/                                 ║
echo ║    │   +-- DOWNLOAD_LINKS.txt                        ║
echo ║    +-- project/          (clean project files)       ║
echo ║                                                      ║
echo ║  ZIP: dist_usb.zip                                   ║
if "%MODE%"=="B" (
echo ║  Note: Client needs Composer + Node to install       ║
) else (
echo ║  Note: Offline-ready, only XAMPP needed              ║
)
echo ╚══════════════════════════════════════════════════════╝
echo.

pause
exit /b 0
