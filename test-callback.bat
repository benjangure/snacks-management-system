@echo off
title M-Pesa Callback Tester
color 0A

echo ========================================
echo    📱 M-PESA CALLBACK TESTER 📱
echo ========================================
echo.

set /p checkoutId="Enter Checkout Request ID (or press Enter for default): "
if "%checkoutId%"=="" set checkoutId=ws_CO_28102025160000000001

echo.
echo Select callback type to test:
echo.
echo 1️⃣  SUCCESS - Payment completed
echo 2️⃣  FAILED - Payment cancelled by user  
echo 3️⃣  TIMEOUT - User unreachable
echo 4️⃣  CUSTOM - Open web tester
echo.

set /p choice="Enter your choice (1-4): "

if "%choice%"=="1" (
    echo.
    echo 🟢 Testing SUCCESS callback...
    curl -X POST "http://127.0.0.1:8001/api/mpesa/callback" ^
    -H "Content-Type: application/json" ^
    -d "{\"Body\":{\"stkCallback\":{\"MerchantRequestID\":\"TEST-001\",\"CheckoutRequestID\":\"%checkoutId%\",\"ResultCode\":0,\"ResultDesc\":\"The service request is processed successfully.\",\"CallbackMetadata\":{\"Item\":[{\"Name\":\"Amount\",\"Value\":100.00},{\"Name\":\"MpesaReceiptNumber\",\"Value\":\"TEST123456\"},{\"Name\":\"TransactionDate\",\"Value\":20251028160000},{\"Name\":\"PhoneNumber\",\"Value\":254700000000}]}}}}"
) else if "%choice%"=="2" (
    echo.
    echo 🔴 Testing FAILED callback...
    curl -X POST "http://127.0.0.1:8001/api/mpesa/callback" ^
    -H "Content-Type: application/json" ^
    -d "{\"Body\":{\"stkCallback\":{\"MerchantRequestID\":\"TEST-002\",\"CheckoutRequestID\":\"%checkoutId%\",\"ResultCode\":1032,\"ResultDesc\":\"Request cancelled by user\"}}}"
) else if "%choice%"=="3" (
    echo.
    echo 🟡 Testing TIMEOUT callback...
    curl -X POST "http://127.0.0.1:8001/api/mpesa/callback" ^
    -H "Content-Type: application/json" ^
    -d "{\"Body\":{\"stkCallback\":{\"MerchantRequestID\":\"TEST-003\",\"CheckoutRequestID\":\"%checkoutId%\",\"ResultCode\":1037,\"ResultDesc\":\"DS timeout user cannot be reached\"}}}"
) else if "%choice%"=="4" (
    echo.
    echo 🌐 Opening web-based callback tester...
    start mandazi-frontend/test-callback.html
    goto end
) else (
    echo ❌ Invalid choice
    goto end
)

echo.
echo ✅ Callback sent! Check Laravel logs for processing details.
echo 📋 To view logs: Get-Content mandazi-backend/storage/logs/laravel.log -Tail 10

:end
echo.
pause