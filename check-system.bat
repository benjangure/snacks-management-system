@echo off
title Mandazi System Status Checker
color 0B

echo ========================================
echo   🔍 MANDAZI SYSTEM STATUS CHECKER
echo ========================================
echo.

echo Checking system components...
echo.

echo 1️⃣  Checking XAMPP MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ MySQL is running
) else (
    echo ❌ MySQL is NOT running - Start XAMPP
)

echo.
echo 2️⃣  Checking Laravel Backend...
netstat -an | find "8001" >NUL
if "%ERRORLEVEL%"=="0" (
    echo ✅ Laravel server is running on port 8001
) else (
    echo ❌ Laravel server is NOT running on port 8001
)

echo.
echo 3️⃣  Checking Frontend Files...
if exist "mandazi-frontend\index.html" (
    echo ✅ Frontend files found
) else (
    echo ❌ Frontend files missing
)

echo.
echo 4️⃣  Testing API Connection...
curl -s http://127.0.0.1:8001/api/test-simple >NUL 2>&1
if "%ERRORLEVEL%"=="0" (
    echo ✅ API is responding
) else (
    echo ❌ API is not responding
)

echo.
echo ========================================
echo 📋 SYSTEM STATUS SUMMARY
echo ========================================

if exist "mandazi-frontend\index.html" (
    if "%ERRORLEVEL%"=="0" (
        echo 🎉 System is ready! You can open index.html
    ) else (
        echo ⚠️  System has issues - check above
    )
) else (
    echo ❌ System not properly set up
)

echo.
echo 🚀 To start system: run start-mandazi-system.bat
echo 📖 For help: read SETUP-GUIDE.md
echo.
pause