@echo off
title Network Troubleshooter for M-Pesa
color 0E

echo ========================================
echo   🌐 M-PESA NETWORK TROUBLESHOOTER
echo ========================================
echo.

echo 1️⃣  Testing DNS Resolution...
nslookup sandbox.safaricom.co.ke
echo.

echo 2️⃣  Testing Connectivity to Safaricom...
ping -n 4 sandbox.safaricom.co.ke
echo.

echo 3️⃣  Testing HTTPS Connection...
curl -I https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials
echo.

echo 4️⃣  Checking Windows Firewall...
netsh advfirewall show allprofiles state
echo.

echo ========================================
echo   🔧 POTENTIAL FIXES
echo ========================================
echo.
echo If DNS fails:
echo   - Try changing DNS to 8.8.8.8 or 1.1.1.1
echo   - Flush DNS: ipconfig /flushdns
echo.
echo If ping fails:
echo   - Check internet connection
echo   - Check corporate firewall/proxy
echo.
echo If HTTPS fails:
echo   - Check Windows Firewall
echo   - Check antivirus blocking
echo.
pause