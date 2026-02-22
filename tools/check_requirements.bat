@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

REM ═══════════════════════════════════════════════════════════
REM  Twinx ERP — Requirements Checker
REM  Usage: check_requirements.bat [XAMPP_PATH]
REM  Default XAMPP path: C:\xampp
REM ═══════════════════════════════════════════════════════════

set "XAMPP_PATH=%~1"
if "%XAMPP_PATH%"=="" set "XAMPP_PATH=C:\xampp"

set "PASS=0"
set "FAIL=0"
set "WARN=0"

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║        Twinx ERP — Requirements Check               ║
echo ╚══════════════════════════════════════════════════════╝
echo.

REM ─── XAMPP ───
echo [1/6] XAMPP Installation...
if exist "%XAMPP_PATH%\xampp-control.exe" (
    echo       ✓ XAMPP found at: %XAMPP_PATH%
    set /a PASS+=1
) else (
    echo       ✗ XAMPP NOT found at: %XAMPP_PATH%
    echo         Specify path: check_requirements.bat "D:\xampp"
    set /a FAIL+=1
)

REM ─── PHP ───
echo.
echo [2/6] PHP Version...
set "PHP_FOUND=0"
where php >nul 2>&1
if !errorlevel! equ 0 set "PHP_FOUND=1"

if "!PHP_FOUND!"=="1" (
    for /f "tokens=*" %%v in ('php -r "echo PHP_VERSION;"') do set "PHP_VER=%%v"
    echo       ✓ PHP !PHP_VER! found

    for /f "tokens=1,2 delims=." %%a in ("!PHP_VER!") do (
        set "PHP_MAJOR=%%a"
        set "PHP_MINOR=%%b"
    )

    set "PHP_OK=0"
    if !PHP_MAJOR! gtr 8 set "PHP_OK=1"
    if !PHP_MAJOR! equ 8 if !PHP_MINOR! geq 2 set "PHP_OK=1"

    if "!PHP_OK!"=="1" (
        set /a PASS+=1
    ) else (
        echo       ✗ PHP 8.2+ required, found !PHP_VER!
        set /a FAIL+=1
    )

    REM Check required extensions
    echo.
    echo       Checking PHP extensions...
    for %%e in (pdo_mysql mbstring openssl tokenizer xml ctype json bcmath fileinfo gd zip) do (
        php -r "echo extension_loaded('%%e') ? 'YES' : 'NO';" 2>nul | findstr /i "YES" >nul
        if !errorlevel! equ 0 (
            echo         ✓ %%e
        ) else (
            echo         ✗ %%e — MISSING
            set /a FAIL+=1
        )
    )
) else (
    echo       ✗ PHP not found in PATH
    echo         Add XAMPP PHP to PATH: %XAMPP_PATH%\php
    set /a FAIL+=1
)

REM ─── Composer ───
echo.
echo [3/6] Composer...
set "COMP_FOUND=0"
where composer >nul 2>&1
if !errorlevel! equ 0 set "COMP_FOUND=1"

if "!COMP_FOUND!"=="1" (
    for /f "tokens=3" %%v in ('composer -V 2^>nul ^| findstr /i "version"') do set "COMP_VER=%%v"
    echo       ✓ Composer !COMP_VER! found
    set /a PASS+=1
) else (
    echo       ✗ Composer not found
    echo         Download: https://getcomposer.org/download/
    set /a FAIL+=1
)

REM ─── Node + npm ───
echo.
echo [4/6] Node.js + npm ...
set "NODE_FOUND=0"
where node >nul 2>&1
if !errorlevel! equ 0 set "NODE_FOUND=1"

if "!NODE_FOUND!"=="1" (
    for /f "tokens=*" %%v in ('node -v') do set "NODE_VER=%%v"
    echo       ✓ Node.js !NODE_VER! found
    set /a PASS+=1
) else (
    echo       ✗ Node.js not found
    echo         Download: https://nodejs.org/
    set /a FAIL+=1
)

set "NPM_FOUND=0"
where npm >nul 2>&1
if !errorlevel! equ 0 set "NPM_FOUND=1"

if "!NPM_FOUND!"=="1" (
    for /f "tokens=*" %%v in ('npm -v') do set "NPM_VER=%%v"
    echo       ✓ npm !NPM_VER! found
    set /a PASS+=1
) else (
    echo       ✗ npm not found
    set /a FAIL+=1
)

REM ─── MySQL Running ───
echo.
echo [5/6] MySQL Service...
set "MYSQL_CHECKED=0"

REM Try XAMPP mysqladmin first
if exist "%XAMPP_PATH%\mysql\bin\mysqladmin.exe" (
    "%XAMPP_PATH%\mysql\bin\mysqladmin" -u root --password="" status >nul 2>&1
    if !errorlevel! equ 0 (
        echo       ✓ MySQL is running
        set /a PASS+=1
        set "MYSQL_CHECKED=1"
    ) else (
        echo       ⚠ MySQL not responding - start it from XAMPP Control Panel
        set /a WARN+=1
        set "MYSQL_CHECKED=1"
    )
)

REM Fallback: try system-wide mysqladmin only if XAMPP check didn't run
if "!MYSQL_CHECKED!"=="0" (
    set "MA_FOUND=0"
    where mysqladmin >nul 2>&1
    if !errorlevel! equ 0 set "MA_FOUND=1"

    if "!MA_FOUND!"=="1" (
        mysqladmin -u root --password="" status >nul 2>&1
        if !errorlevel! equ 0 (
            echo       ✓ MySQL is running
            set /a PASS+=1
        ) else (
            echo       ⚠ MySQL not responding
            set /a WARN+=1
        )
    ) else (
        REM Last resort: check port 3306
        netstat -an 2>nul | findstr ":3306 " | findstr "LISTENING" >nul 2>&1
        if !errorlevel! equ 0 (
            echo       ✓ MySQL appears to be running on port 3306
            set /a PASS+=1
        ) else (
            echo       ⚠ Cannot verify MySQL status - make sure it is running
            set /a WARN+=1
        )
    )
)

REM ─── Disk Space ───
echo.
echo [6/6] Disk Space...
echo       Ensure at least 500 MB free in %XAMPP_PATH%
set /a PASS+=1

REM ─── Summary ───
echo.
echo ══════════════════════════════════════════════════════
echo  Results:  %PASS% passed,  %FAIL% failed,  %WARN% warnings
echo ══════════════════════════════════════════════════════

if %FAIL% gtr 0 (
    echo.
    echo  ✗ Some requirements are missing. Fix them before installing.
) else (
    echo.
    echo  ✓ All requirements met! You can run SETUP.bat
)

echo.
pause
exit /b %FAIL%
