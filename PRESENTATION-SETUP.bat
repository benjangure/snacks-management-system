@echo off
title Mandazi System - Presentation Setup
color 0A

echo ========================================
echo   🎯 MANDAZI PRESENTATION SETUP 🎯
echo ========================================
echo.

echo 📋 Pre-Presentation Checklist:
echo.

echo 1️⃣  Stopping any running processes...
taskkill /f /im php.exe 2>nul
taskkill /f /im ngrok.exe 2>nul
timeout /t 2 /nobreak >nul

echo 2️⃣  Starting XAMPP MySQL...
cd /d "C:\xampp"
start /min xampp-control.exe
timeout /t 3 /nobreak >nul

echo 3️⃣  Starting Laravel Backend (Port 8001)...
cd /d "%~dp0mandazi-backend"
start /min cmd /c "title Laravel Server && php artisan serve --port=8001"
timeout /t 5 /nobreak >nul

echo 4️⃣  Starting ngrok tunnel...
start /min cmd /c "title ngrok Tunnel && ngrok http 8001"
timeout /t 5 /nobreak >nul

echo 5️⃣  Opening presentation tools...
start /min "%~dp0mandazi-frontend\demo-login.html"
start /min "%~dp0mandazi-frontend\test-callback.html"

echo.
echo ✅ SYSTEM READY FOR PRESENTATION!
echo.
echo 🌐 Frontend: demo-login.html (cache-free)
echo 🔧 Backend: http://127.0.0.1:8001
echo 📱 ngrok: Check ngrok window for URL
echo 🧪 Callback Tester: test-callback.html
echo.
echo 📋 Quick Demo Credentials:
echo    Buyer: johnbuyer / password123
echo    Seller: janeseller / password123
echo.
echo 🎯 PRESENTATION FLOW:
echo    1. Login Demo (demo-login.html)
echo    2. Show Features (buyer/seller dashboards)
echo    3. STK Push Demo (place order → pay)
echo    4. Callback Testing (test-callback.html)
echo.
echo Press any key when ready to start presentation...
pause >nul

echo.
echo 🚀 Opening main demo page...
start "%~dp0mandazi-frontend\demo-login.html"

echo.
echo 📊 System Status Check:
netstat -an | find "8001" >nul && echo ✅ Laravel: Running || echo ❌ Laravel: Not running
curl -s http://127.0.0.1:8001/api/test-simple >nul 2>&1 && echo ✅ API: Responding || echo ❌ API: Not responding

echo.
echo 🎉 READY TO PRESENT! Good luck!
pause