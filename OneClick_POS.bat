@echo off
echo Starting Twinx POS with Silent Printing...
echo.
echo IMPORTANT: Please ensure all other Chrome windows are closed first!
echo.
taskkill /F /IM chrome.exe >nul 2>&1
timeout /t 1 >nul
start chrome --kiosk-printing --app=http://127.0.0.1:8000/pos
exit
