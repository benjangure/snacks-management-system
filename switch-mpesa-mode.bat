@echo off
title M-Pesa Mode Switcher
color 0E

echo ========================================
echo     📱 M-PESA MODE SWITCHER 📱
echo ========================================
echo.

echo Current mode in .env file:
findstr "MPESA_MODE" mandazi-backend\.env
echo.

echo Select M-Pesa mode:
echo.
echo 1️⃣  REAL - Send actual STK push (requires internet)
echo 2️⃣  SIMULATION - Demo mode (works offline)
echo 3️⃣  AUTO - Try real, fallback to simulation
echo.

set /p choice="Enter your choice (1-3): "

cd mandazi-backend

if "%choice%"=="1" (
    echo Setting mode to REAL...
    powershell -Command "(Get-Content .env) -replace 'MPESA_MODE=.*', 'MPESA_MODE=real' | Set-Content .env"
    echo ✅ Mode set to REAL - Will send actual STK push
) else if "%choice%"=="2" (
    echo Setting mode to SIMULATION...
    powershell -Command "(Get-Content .env) -replace 'MPESA_MODE=.*', 'MPESA_MODE=simulation' | Set-Content .env"
    echo ✅ Mode set to SIMULATION - Will use demo mode
) else if "%choice%"=="3" (
    echo Setting mode to AUTO...
    powershell -Command "(Get-Content .env) -replace 'MPESA_MODE=.*', 'MPESA_MODE=auto' | Set-Content .env"
    echo ✅ Mode set to AUTO - Will try real, fallback to simulation
) else (
    echo ❌ Invalid choice
    goto end
)

echo.
echo 🔄 Restart Laravel server for changes to take effect
echo.

:end
pause