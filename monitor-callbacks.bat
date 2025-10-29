@echo off
title M-Pesa Callback Monitor
color 0F

echo ========================================
echo   📡 M-PESA CALLBACK MONITOR 📡
echo ========================================
echo.
echo Monitoring Laravel logs for M-Pesa callbacks...
echo Press Ctrl+C to stop monitoring
echo.

cd mandazi-backend
powershell -Command "Get-Content storage/logs/laravel.log -Wait -Tail 0 | Where-Object { $_ -match 'CALLBACK|STK|M-Pesa|Payment' }"