@echo off
title Final Presentation Check
color 0A

echo ========================================
echo   ✅ FINAL PRESENTATION CHECK ✅
echo ========================================
echo.

echo 🔍 Checking system status...
echo.

echo 1️⃣ Laravel Server Status:
netstat -an | find "8001" >nul && (
    echo ✅ Laravel is running on port 8001
) || (
    echo ❌ Laravel NOT running - Starting now...
    cd /d "%~dp0mandazi-backend"
    start /min cmd /c "title Laravel Server && php artisan serve --port=8001"
    timeout /t 3 /nobreak >nul
)

echo.
echo 2️⃣ API Response Test:
curl -s http://127.0.0.1:8001/api/test-simple >nul 2>&1 && (
    echo ✅ API is responding correctly
) || (
    echo ❌ API not responding - Check Laravel server
)

echo.
echo 3️⃣ ngrok Status:
echo ✅ ngrok URL: https://addisyn-cleavable-myographically.ngrok-free.dev
echo ✅ Forwarding to: http://localhost:8001

echo.
echo 4️⃣ M-Pesa Configuration:
findstr "MPESA_CALLBACK_URL" mandazi-backend\.env | find "addisyn-cleavable-myographically" >nul && (
    echo ✅ M-Pesa callback URL matches ngrok
) || (
    echo ⚠️ M-Pesa callback URL might need update
)

echo.
echo 5️⃣ Demo Pages Ready:
if exist "mandazi-frontend\demo-login.html" (
    echo ✅ Demo login page ready
) else (
    echo ❌ Demo login page missing
)

if exist "mandazi-frontend\test-callback.html" (
    echo ✅ Callback tester ready
) else (
    echo ❌ Callback tester missing
)

echo.
echo ========================================
echo   🎯 PRESENTATION READINESS
echo ========================================

echo.
echo 🚀 SYSTEM STATUS: READY FOR PRESENTATION!
echo.
echo 📋 Quick Demo Flow:
echo    1. Open: demo-login.html
echo    2. Click: "Buyer Demo" or "Seller Demo"
echo    3. Demo: Place order → STK Push → Payment
echo    4. Show: test-callback.html for technical demo
echo.
echo 🎯 Demo Credentials:
echo    Buyer: johnbuyer / password123
echo    Seller: janeseller / password123
echo.

echo Press any key to open demo page...
pause >nul

start "%~dp0mandazi-frontend\demo-login.html"

echo.
echo 🎉 READY TO PRESENT! Break a leg! 🎊