@echo off
echo ========================================
echo   MANDAZI SYSTEM - CACHE CLEANER
echo ========================================
echo.

echo 🧹 Clearing browser cache instructions:
echo.
echo Chrome/Edge:
echo   Press Ctrl+Shift+Delete
echo   Select "All time" and check all boxes
echo   Click "Clear data"
echo.
echo Firefox:
echo   Press Ctrl+Shift+Delete
echo   Select "Everything" and check all boxes
echo   Click "Clear Now"
echo.

echo 🔄 Restarting Laravel server on port 8001...
cd mandazi-backend
taskkill /f /im php.exe 2>nul
timeout /t 2 /nobreak >nul
start /b php artisan serve --port=8001
echo.

echo ✅ Server restarted!
echo 🌐 Use demo-login.html for cache-free demonstrations
echo.
pause