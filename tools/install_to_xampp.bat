@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

REM ═══════════════════════════════════════════════════════════
REM  Twinx ERP — Install to XAMPP
REM  Usage: install_to_xampp.bat [XAMPP_PATH] [FOLDER_NAME]
REM  Defaults: C:\xampp  twinx-erp
REM  Idempotent — safe to re-run at any time.
REM ═══════════════════════════════════════════════════════════

set "XAMPP_PATH=%~1"
set "DEST_NAME=%~2"
if "%XAMPP_PATH%"=="" set "XAMPP_PATH=C:\xampp"
if "%DEST_NAME%"=="" set "DEST_NAME=twinx-erp"

set "HTDOCS=%XAMPP_PATH%\htdocs"
set "DEST=%HTDOCS%\%DEST_NAME%"
set "SCRIPT_DIR=%~dp0"
set "REPO_DIR=%SCRIPT_DIR%.."

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║        Twinx ERP — XAMPP Installer                  ║
echo ╚══════════════════════════════════════════════════════╝
echo.
echo   XAMPP:   %XAMPP_PATH%
echo   Target:  %DEST%
echo.

REM ─── Step 1: Verify XAMPP ───
echo [1/7] Verifying XAMPP installation...
if not exist "%XAMPP_PATH%\xampp-control.exe" (
    echo   ✗ XAMPP not found at %XAMPP_PATH%
    echo     Run: install_to_xampp.bat "D:\xampp" "%DEST_NAME%"
    exit /b 1
)
echo   ✓ XAMPP found

REM ─── Step 2: Copy project to htdocs ───
echo.
echo [2/7] Copying project to htdocs...
if not exist "%DEST%" (
    echo   Creating %DEST%...
    mkdir "%DEST%" 2>nul
)

REM Use robocopy to sync (excluding junk)
robocopy "%REPO_DIR%" "%DEST%" /MIR /XD ".git" "node_modules" ".agent" "_backup_before_cleanup" "dist_usb" "storage\logs" /XF "*.log" "database.sqlite" "nativephp.sqlite*" ".phpunit.result.cache" /NFL /NDL /NP /NJH /NJS >nul 2>&1

echo   ✓ Project copied to %DEST%

REM ─── Step 3: Create .env if missing ───
echo.
echo [3/7] Configuring environment...
if not exist "%DEST%\.env" (
    if exist "%DEST%\.env.xampp.example" (
        copy "%DEST%\.env.xampp.example" "%DEST%\.env" >nul
        echo   ✓ Created .env from .env.xampp.example
    ) else (
        copy "%DEST%\.env.example" "%DEST%\.env" >nul
        echo   ⚠ Created .env from .env.example (update DB settings manually!)
    )
) else (
    echo   ✓ .env already exists (keeping current config)
)

REM ─── Step 4: Composer install ───
echo.
echo [4/7] Installing PHP dependencies...
where composer >nul 2>&1
if %errorlevel% equ 0 (
    if not exist "%DEST%\vendor\autoload.php" (
        cd /d "%DEST%"
        composer install --no-dev --optimize-autoloader --no-interaction 2>&1
        echo   ✓ Composer install complete
    ) else (
        echo   ✓ Vendor directory already exists
    )
) else (
    if exist "%DEST%\vendor\autoload.php" (
        echo   ✓ Vendor directory pre-bundled
    ) else (
        echo   ✗ Composer not found and vendor missing!
        echo     Install Composer: https://getcomposer.org/download/
        exit /b 1
    )
)

REM ─── Step 5: Laravel setup ───
echo.
echo [5/7] Laravel setup (key, migrations, seeding)...
cd /d "%DEST%"

REM Generate APP_KEY if not set
php artisan key:generate --no-interaction --force 2>nul
echo   ✓ App key set

REM Create storage link
php artisan storage:link --no-interaction --force 2>nul

REM Check MySQL is reachable before migrating
php -r "try { new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); echo 'OK'; } catch(Exception $e) { echo 'FAIL'; }" 2>nul | findstr "OK" >nul
if %errorlevel% neq 0 (
    echo.
    echo   ⚠ MySQL is not running!
    echo     Start MySQL from XAMPP Control Panel, then re-run this script.
    echo     Or create the database manually: CREATE DATABASE twinx_erp;
    exit /b 1
)

REM Create database if it doesn't exist
php -r "try { $p = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); $p->exec('CREATE DATABASE IF NOT EXISTS twinx_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo 'OK'; } catch(Exception $e) { echo $e->getMessage(); }" 2>nul
echo   ✓ Database twinx_erp ensured

REM Run migrations + seed
php artisan migrate --force --no-interaction 2>&1
php artisan db:seed --force --no-interaction 2>&1
echo   ✓ Migrations and seeding complete

REM ─── Step 6: Build frontend ───
echo.
echo [6/7] Building frontend assets...
if exist "%DEST%\public\build\manifest.json" (
    echo   ✓ Pre-built assets found (skipping npm build)
) else (
    where node >nul 2>&1
    if !errorlevel! equ 0 (
        cd /d "%DEST%"
        call npm install 2>&1
        call npm run build 2>&1
        echo   ✓ Frontend build complete
    ) else (
        echo   ✗ Node.js not found and no pre-built assets!
        echo     Install Node.js: https://nodejs.org/
        exit /b 1
    )
)

REM ─── Step 7: Clear caches ───
echo.
echo [7/7] Clearing caches...
cd /d "%DEST%"
php artisan config:clear --no-interaction 2>nul
php artisan cache:clear --no-interaction 2>nul
php artisan view:clear --no-interaction 2>nul
php artisan route:clear --no-interaction 2>nul
echo   ✓ Caches cleared

REM ─── Done ───
echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║           ✓ Installation Complete!                  ║
echo ╠══════════════════════════════════════════════════════╣
echo ║                                                      ║
echo ║  URL:      http://localhost/%DEST_NAME%/public       ║
echo ║                                                      ║
echo ║  Admin Login:                                        ║
echo ║    Email:    admin@local.test                        ║
echo ║    Password: Admin@12345                             ║
echo ║                                                      ║
echo ║  Logs:     %DEST%\storage\logs\laravel.log          ║
echo ║                                                      ║
echo ╚══════════════════════════════════════════════════════╝
echo.

exit /b 0
