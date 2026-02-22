@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion
title Twinx ERP - Update Patch
color 0B

echo.
echo   Twinx ERP - Applying Updates...
echo ==========================================
echo.
echo   [1/4] Finding Installation...

set "TARGET_DIR="
if exist "C:\xampp2\htdocs\twinx-erp\.env" set "TARGET_DIR=C:\xampp2\htdocs\twinx-erp"
if "%TARGET_DIR%"=="" if exist "C:\xampp\htdocs\twinx-erp\.env" set "TARGET_DIR=C:\xampp\htdocs\twinx-erp"

if "%TARGET_DIR%"=="" (
    echo.
    echo   ERROR: Could not find Twinx installation!
    echo.
    pause
    exit /b 1
)

echo   Found at: !TARGET_DIR!

echo.
echo   [2/4] Updating Files...

set "SOURCE_DIR=%~dp0project"

if not exist "!SOURCE_DIR!" (
    echo   ERROR: Project source folder not found!
    echo   Expected: !SOURCE_DIR!
    pause
    exit /b 1
)

REM Copy Receipt Views (Fixes Logo)
copy /y "!SOURCE_DIR!\resources\views\pos\receipt.blade.php" "!TARGET_DIR!\resources\views\pos\" >nul
if %errorlevel% neq 0 echo   - Failed to update receipt.blade.php

copy /y "!SOURCE_DIR!\resources\views\pos\receipt_delivery.blade.php" "!TARGET_DIR!\resources\views\pos\" >nul
if %errorlevel% neq 0 echo   - Failed to update receipt_delivery.blade.php

REM Copy Launch Scripts (Fixes Printing)
copy /y "!SOURCE_DIR!\tools\launch_twinx.bat" "!TARGET_DIR!\tools\" >nul
if %errorlevel% neq 0 echo   - Failed to update launch_twinx.bat

copy /y "!SOURCE_DIR!\tools\create_shortcut.ps1" "!TARGET_DIR!\tools\" >nul
if %errorlevel% neq 0 echo   - Failed to update create_shortcut.ps1

REM Copy P&L Report Fix (Returns & Discounts)
copy /y "!SOURCE_DIR!\Modules\Reporting\Services\FinancialReportService.php" "!TARGET_DIR!\Modules\Reporting\Services\" >nul
if %errorlevel% neq 0 echo   - Failed to update FinancialReportService.php

copy /y "!SOURCE_DIR!\resources\views\reports\financial\profit-loss.blade.php" "!TARGET_DIR!\resources\views\reports\financial\" >nul
if %errorlevel% neq 0 echo   - Failed to update profit-loss.blade.php

echo   Files updated.

echo.
echo   [3/4] Clearing Cache...
cd /d "!TARGET_DIR!"
call php artisan view:clear >nul 2>&1
echo   View cache cleared.

echo.
echo   [4/4] Updating Shortcut...
if exist "!TARGET_DIR!\tools\create_shortcut.ps1" (
    powershell -NoProfile -ExecutionPolicy Bypass -File "!TARGET_DIR!\tools\create_shortcut.ps1" -ProjectPath "!TARGET_DIR!"
    echo   Shortcut updated.
)

echo.
echo ==========================================
echo   UPDATE COMPLETE
echo ==========================================
echo.
echo   Please close Twinx and open it again.
echo.
pause
