@echo off
echo Opening Twinx POS in Kiosk/Silent Print Mode...
start chrome --kiosk-printing --app=http://127.0.0.1:8000/pos
exit
