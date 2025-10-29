@echo off
title Quick System Restart
color 0C

echo ========================================
echo   ⚡ QUICK SYSTEM RESTART ⚡
echo ========================================
echo.

echo 🛑 Stopping all processes...
taskkill /f /im php.exe 2>nul
taskkill /f /im ngrok.exe 2>nul
echo.

echo 🔄 Restarting Laravel (Port 8001)...
cd /d "%~dp0mandazi-backend"
start /min cmd /c "title Laravel Server && php artisan serve --port=8001"
timeout /t 3 /nobreak >nul

echo 🔄 Restarting ngrok...
start /min cmd /c "title ngrok Tunnel && ngrok http 8001"
timeout /t 3 /nobreak >nul

echo.
echo ✅ System restarted!
echo 🌐 Demo page: demo-login.html
echo.

start "%~dp0mandazi-frontend\demo-login.html"
pause