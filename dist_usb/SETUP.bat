@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

REM ═══════════════════════════════════════════════════════════
REM  Twinx ERP — MASTER ONE-CLICK SETUP
REM  
REM  This is the ONLY file the client needs to double-click.
REM  It handles EVERYTHING automatically:
REM    1. Detects/Finds XAMPP
REM    2. Starts Apache + MySQL
REM    3. Copies project to htdocs
REM    4. Creates database
REM    5. Configures .env
REM    6. Runs migrations + seeds admin user
REM    7. Creates desktop shortcut with custom icon
REM    8. Opens app (looks like desktop application)
REM
REM  Idempotent — safe to run multiple times.
REM ═══════════════════════════════════════════════════════════

title Twinx ERP — Setup
color 0B

echo.
echo  ████████╗██╗    ██╗██╗███╗   ██╗██╗  ██╗
echo  ╚══██╔══╝██║    ██║██║████╗  ██║╚██╗██╔╝
echo     ██║   ██║ █╗ ██║██║██╔██╗ ██║ ╚███╔╝ 
echo     ██║   ██║███╗██║██║██║╚██╗██║ ██╔██╗ 
echo     ██║   ╚███╔███╔╝██║██║ ╚████║██╔╝ ██╗
echo     ╚═╝    ╚══╝╚══╝ ╚═╝╚═╝  ╚═══╝╚═╝  ╚═╝
echo.
echo          Twinx ERP - Auto Setup
echo ══════════════════════════════════════════
echo.

set "SCRIPT_DIR=%~dp0"
set "PROJECT_DIR=%SCRIPT_DIR%project"
set "DEST_NAME=twinx-erp"

REM ─── Check if project folder exists ───
if not exist "%PROJECT_DIR%" (
    REM Maybe the project is in the same folder as SETUP.bat
    if exist "%SCRIPT_DIR%artisan" (
        set "PROJECT_DIR=%SCRIPT_DIR%"
    ) else (
        echo   ✗ ERROR: Cannot find project files!
        echo     Expected folder: %PROJECT_DIR%
        echo.
        goto :show_error
    )
)

REM ═══════════════════════════════════════════
REM  STEP 1: Find XAMPP
REM ═══════════════════════════════════════════
echo [1/9] Looking for XAMPP...

set "XAMPP_PATH="

REM Check common locations
if exist "C:\xampp\xampp-control.exe" set "XAMPP_PATH=C:\xampp"
if "%XAMPP_PATH%"=="" if exist "D:\xampp\xampp-control.exe" set "XAMPP_PATH=D:\xampp"
if "%XAMPP_PATH%"=="" if exist "E:\xampp\xampp-control.exe" set "XAMPP_PATH=E:\xampp"
if "%XAMPP_PATH%"=="" if exist "%ProgramFiles%\xampp\xampp-control.exe" set "XAMPP_PATH=%ProgramFiles%\xampp"
if "%XAMPP_PATH%"=="" if exist "%ProgramFiles(x86)%\xampp\xampp-control.exe" set "XAMPP_PATH=%ProgramFiles(x86)%\xampp"

if "%XAMPP_PATH%"=="" (
    echo.
    echo   ╔══════════════════════════════════════════════════╗
    echo   ║  ✗ XAMPP is NOT INSTALLED!                       ║
    echo   ║                                                  ║
    echo   ║  Please install XAMPP first, then run this       ║
    echo   ║  script again.                                   ║
    echo   ╚══════════════════════════════════════════════════╝
    echo.
    
    REM Try to open download links file
    if exist "%SCRIPT_DIR%requirements\DOWNLOAD_LINKS.txt" (
        echo   Opening download links...
        start notepad "%SCRIPT_DIR%requirements\DOWNLOAD_LINKS.txt"
    ) else (
        echo   Download XAMPP from: https://www.apachefriends.org/
    )
    echo.
    goto :show_error
)

echo   ✓ XAMPP found at: %XAMPP_PATH%

REM ═══════════════════════════════════════════
REM  STEP 2: Add PHP to PATH temporarily
REM ═══════════════════════════════════════════
echo.
echo [2/9] Configuring PHP path...
set "PATH=%XAMPP_PATH%\php;%XAMPP_PATH%\mysql\bin;%PATH%"
echo   ✓ PHP path set to: %XAMPP_PATH%\php

REM Quick PHP version check
"%XAMPP_PATH%\php\php.exe" -r "echo PHP_VERSION;" > "%TEMP%\twinx_phpver.txt" 2>nul
set "PHP_VER="
set /p PHP_VER=<"%TEMP%\twinx_phpver.txt"
del "%TEMP%\twinx_phpver.txt" 2>nul

if defined PHP_VER (
    echo   ✓ PHP version: !PHP_VER!
) else (
    echo   ✗ Cannot detect PHP version
    goto :show_error
)

REM ═══════════════════════════════════════════
REM  STEP 3: Start Apache + MySQL
REM ═══════════════════════════════════════════
echo.
echo [3/9] Starting Apache and MySQL...

REM Check if Apache is already running
netstat -an 2>nul | findstr ":80 " | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo   ✓ Apache already running on port 80
) else (
    if exist "%XAMPP_PATH%\apache\bin\httpd.exe" (
        start /B "" "%XAMPP_PATH%\apache\bin\httpd.exe" >nul 2>&1
        timeout /t 3 /nobreak >nul
        echo   ✓ Apache started
    ) else (
        echo   ⚠ Cannot auto-start Apache. Start it from XAMPP Control Panel.
    )
)

REM Check if MySQL is already running
netstat -an 2>nul | findstr ":3306 " | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo   ✓ MySQL already running on port 3306
) else (
    if exist "%XAMPP_PATH%\mysql\bin\mysqld.exe" (
        start /B "" "%XAMPP_PATH%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_PATH%\mysql\bin\my.ini" >nul 2>&1
        timeout /t 5 /nobreak >nul
        echo   ✓ MySQL started
    ) else (
        echo   ⚠ Cannot auto-start MySQL. Start it from XAMPP Control Panel.
    )
)

REM Wait for services
timeout /t 2 /nobreak >nul

REM Verify MySQL is reachable
"%XAMPP_PATH%\php\php.exe" -r "try { new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); echo 'OK'; } catch(Exception $e) { echo 'FAIL'; }" 2>nul | findstr "OK" >nul
if %errorlevel% neq 0 (
    echo.
    echo   ╔══════════════════════════════════════════════════╗
    echo   ║  ⚠ MySQL is not responding!                     ║
    echo   ║                                                  ║
    echo   ║  Please start MySQL from XAMPP Control Panel     ║
    echo   ║  and then run this script again.                 ║
    echo   ╚══════════════════════════════════════════════════╝
    echo.
    start "" "%XAMPP_PATH%\xampp-control.exe"
    goto :show_error
)
echo   ✓ MySQL is responding

REM ═══════════════════════════════════════════
REM  STEP 4: Copy project to htdocs
REM ═══════════════════════════════════════════
echo.
echo [4/9] Deploying project to XAMPP...

set "DEST=%XAMPP_PATH%\htdocs\%DEST_NAME%"

if not exist "%DEST%" mkdir "%DEST%"

robocopy "%PROJECT_DIR%" "%DEST%" /MIR /XD ".git" "node_modules" ".agent" "_backup_before_cleanup" "dist_usb" /XF "*.log" "SETUP.bat" ".env" "database.sqlite" /NFL /NDL /NP /NJH /NJS >nul 2>&1

REM Copy favicon to XAMPP root so Chrome app mode picks it up
if exist "%DEST%\public\favicon.ico" (
    copy "%DEST%\public\favicon.ico" "%XAMPP_PATH%\htdocs\favicon.ico" >nul 2>&1
)

echo   ✓ Project deployed to: %DEST%

REM ═══════════════════════════════════════════
REM  STEP 5: Configure .env
REM ═══════════════════════════════════════════
echo.
echo [5/9] Configuring environment...

if not exist "%DEST%\.env" (
    if exist "%DEST%\.env.xampp.example" (
        copy "%DEST%\.env.xampp.example" "%DEST%\.env" >nul
        echo   ✓ Environment configured - MySQL mode
    ) else if exist "%DEST%\.env.example" (
        copy "%DEST%\.env.example" "%DEST%\.env" >nul
        echo   ⚠ Created .env from default template
    )
) else (
    echo   ✓ Environment already configured
)

cd /d "%DEST%"
"%XAMPP_PATH%\php\php.exe" artisan key:generate --no-interaction --force >nul 2>&1
echo   ✓ App key set
"%XAMPP_PATH%\php\php.exe" artisan storage:link --no-interaction --force >nul 2>&1

REM ═══════════════════════════════════════════
REM  STEP 6: Create database
REM ═══════════════════════════════════════════
echo.
echo [6/9] Setting up database...

"%XAMPP_PATH%\php\php.exe" -r "try { $p = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); $p->exec('CREATE DATABASE IF NOT EXISTS twinx_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); echo 'OK'; } catch(Exception $e) { echo $e->getMessage(); }" 2>nul
echo   ✓ Database 'twinx_erp' ready

REM ═══════════════════════════════════════════
REM  STEP 7: Run migrations + seed
REM ═══════════════════════════════════════════
echo.
echo [7/9] Running database migrations and seeding...
echo       This may take a minute on first run...
echo.

cd /d "%DEST%"
"%XAMPP_PATH%\php\php.exe" artisan migrate --force --no-interaction 2>&1
echo.
"%XAMPP_PATH%\php\php.exe" artisan db:seed --force --no-interaction 2>&1

REM Clear caches
"%XAMPP_PATH%\php\php.exe" artisan config:clear --no-interaction >nul 2>&1
"%XAMPP_PATH%\php\php.exe" artisan cache:clear --no-interaction >nul 2>&1
"%XAMPP_PATH%\php\php.exe" artisan view:clear --no-interaction >nul 2>&1
"%XAMPP_PATH%\php\php.exe" artisan route:clear --no-interaction >nul 2>&1

echo.
echo   ✓ Database setup complete

REM ═══════════════════════════════════════════
REM  STEP 8: Create desktop shortcut
REM ═══════════════════════════════════════════
echo.
echo [8/9] Creating desktop shortcut...

set "APP_URL=http://localhost/%DEST_NAME%/public"

REM Run PowerShell script to create shortcut with custom icon + silent printing
if exist "%DEST%\tools\create_shortcut.ps1" (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%DEST%\tools\create_shortcut.ps1" -ProjectPath "%DEST%" -AppUrl "%APP_URL%"
) else if exist "%PROJECT_DIR%\tools\create_shortcut.ps1" (
    powershell -NoProfile -ExecutionPolicy Bypass -File "%PROJECT_DIR%\tools\create_shortcut.ps1" -ProjectPath "%DEST%" -AppUrl "%APP_URL%"
) else (
    echo   ⚠ Shortcut script not found, creating basic shortcut...
    REM Fallback: create shortcut pointing to silent VBS launcher
    powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws=New-Object -COM WScript.Shell; $s=$ws.CreateShortcut([Environment]::GetFolderPath('Desktop')+'\Twinx ERP.lnk'); $s.TargetPath=(Get-Command wscript.exe).Source; $s.Arguments='\"'+'%DEST%\tools\launch_twinx.vbs'+'\"'; $s.WindowStyle=1; $s.Save()"
    echo   ✓ Basic shortcut created
)

REM ═══════════════════════════════════════════
REM  STEP 9: Launch application
REM ═══════════════════════════════════════════
echo.
echo [9/9] Launching Twinx ERP...

REM Find browser and open in app mode
set "BROWSER_EXE="
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    set "BROWSER_EXE=%ProgramFiles%\Google\Chrome\Application\chrome.exe"
)
if "!BROWSER_EXE!"=="" if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    set "BROWSER_EXE=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
)
if "!BROWSER_EXE!"=="" if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" (
    set "BROWSER_EXE=%LocalAppData%\Google\Chrome\Application\chrome.exe"
)
REM Fallback to Edge
if "!BROWSER_EXE!"=="" (
    for /f "tokens=*" %%p in ('where msedge.exe 2^>nul') do set "BROWSER_EXE=%%p"
)
if "!BROWSER_EXE!"=="" (
    for /f "tokens=*" %%p in ('dir /s /b "%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe" 2^>nul') do set "BROWSER_EXE=%%p"
)

if defined BROWSER_EXE (
    start "" "!BROWSER_EXE!" --app=%APP_URL% --kiosk-printing --start-maximized --disable-infobars
    echo   ✓ App launched in desktop mode
) else (
    start "" "%APP_URL%"
    echo   ✓ App opened in default browser
)

echo.
echo ╔══════════════════════════════════════════════════════════╗
echo ║                                                          ║
echo ║        ✅ Twinx ERP is READY!                           ║
echo ║                                                          ║
echo ╠══════════════════════════════════════════════════════════╣
echo ║                                                          ║
echo ║   URL:      %APP_URL%
echo ║                                                          ║
echo ║   Admin Login:                                           ║
echo ║      Email:    admin@local.test                          ║
echo ║      Password: Admin@12345                               ║
echo ║                                                          ║
echo ║   Desktop shortcut "Twinx ERP" on your desktop          ║
echo ║   POS printing: SILENT - no dialog                      ║
echo ║                                                          ║
echo ╚══════════════════════════════════════════════════════════╝
echo.
echo   Press any key to close this window...
pause >nul
exit /b 0

:show_error
echo.
echo   Setup could not complete.
echo   Fix the issue above and run SETUP.bat again.
echo.
echo   Press any key to close...
pause >nul
exit /b 1
