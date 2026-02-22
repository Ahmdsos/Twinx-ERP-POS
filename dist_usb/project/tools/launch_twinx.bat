@echo off
chcp 65001 >nul 2>&1
setlocal enabledelayedexpansion

REM ═══════════════════════════════════════════════════════════
REM  Twinx ERP — Smart Launcher
REM  Auto-starts Apache + MySQL if they're not running,
REM  then opens the app in desktop mode.
REM ═══════════════════════════════════════════════════════════

title Twinx ERP

REM ─── Find XAMPP ───
set "XAMPP_PATH="
if exist "C:\xampp\xampp-control.exe" set "XAMPP_PATH=C:\xampp"
if "%XAMPP_PATH%"=="" if exist "D:\xampp\xampp-control.exe" set "XAMPP_PATH=D:\xampp"
if "%XAMPP_PATH%"=="" if exist "E:\xampp\xampp-control.exe" set "XAMPP_PATH=E:\xampp"
if "%XAMPP_PATH%"=="" if exist "%ProgramFiles%\xampp\xampp-control.exe" set "XAMPP_PATH=%ProgramFiles%\xampp"
if "%XAMPP_PATH%"=="" if exist "%ProgramFiles(x86)%\xampp\xampp-control.exe" set "XAMPP_PATH=%ProgramFiles(x86)%\xampp"

if "%XAMPP_PATH%"=="" (
    echo XAMPP not found! Please install XAMPP first.
    pause
    exit /b 1
)

REM ─── Start Apache if not running ───
netstat -an 2>nul | findstr ":80 " | findstr "LISTENING" >nul 2>&1
if %errorlevel% neq 0 (
    if exist "%XAMPP_PATH%\apache\bin\httpd.exe" (
        start /B "" "%XAMPP_PATH%\apache\bin\httpd.exe" >nul 2>&1
    )
)

REM ─── Start MySQL if not running ───
netstat -an 2>nul | findstr ":3306 " | findstr "LISTENING" >nul 2>&1
if %errorlevel% neq 0 (
    if exist "%XAMPP_PATH%\mysql\bin\mysqld.exe" (
        start /B "" "%XAMPP_PATH%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_PATH%\mysql\bin\my.ini" >nul 2>&1
    )
)

REM ─── Wait for services to be ready ───
set "RETRIES=0"
:wait_loop
if !RETRIES! geq 15 goto :open_app
timeout /t 1 /nobreak >nul
set /a RETRIES+=1

REM Check both services
set "APACHE_OK=0"
set "MYSQL_OK=0"
netstat -an 2>nul | findstr ":80 " | findstr "LISTENING" >nul 2>&1 && set "APACHE_OK=1"
netstat -an 2>nul | findstr ":3306 " | findstr "LISTENING" >nul 2>&1 && set "MYSQL_OK=1"

if "!APACHE_OK!"=="1" if "!MYSQL_OK!"=="1" goto :open_app
goto :wait_loop

:open_app
REM ─── Small extra delay for MySQL to fully initialize ───
timeout /t 1 /nobreak >nul

REM ─── Find Chrome or Edge ───
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

set "APP_URL=http://localhost/twinx-erp/public"

if defined BROWSER_EXE (
    start "" "!BROWSER_EXE!" --app=%APP_URL% --start-maximized --disable-infobars
) else (
    start "" "%APP_URL%"
)

exit /b 0
