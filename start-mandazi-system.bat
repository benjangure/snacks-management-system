@echo off
title Mandazi System Startup
color 0A

echo ========================================
echo    🍞 MANDAZI MANAGEMENT SYSTEM 🍞
echo ========================================
echo.

echo 📋 Starting system components...
echo.

echo 1️⃣  Starting XAMPP MySQL...
cd /d "C:\xampp"
start /min xampp-control.exe
timeout /t 3 /nobreak >nul

echo 2️⃣  Starting Laravel Backend Server...
cd /d "%~dp0mandazi-backend"
start /min cmd /c "php artisan serve --port=8001"
timeout /t 3 /nobreak >nul

echo 3️⃣  Opening Frontend...
cd /d "%~dp0mandazi-frontend"
start index.html

echo.
echo ✅ System Started Successfully!
echo.
echo 🌐 Frontend: Opens automatically
echo 🔧 Backend: http://127.0.0.1:8001
echo 💾 Database: XAMPP MySQL
echo.
echo 📱 ngrok command (run separately):
echo    ngrok http 8001
echo.
echo Press any key to exit...
pause >nul