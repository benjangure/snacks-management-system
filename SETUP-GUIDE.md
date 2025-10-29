# 🍞 Mandazi System - Quick Setup Guide

## 🚀 One-Click Startup

### Method 1: Automatic Startup (Recommended)
1. **Double-click**: `start-mandazi-system.bat`
2. **Wait**: System starts automatically
3. **Login**: Browser opens with login page

### Method 2: Manual Startup
1. **Start XAMPP**: Open XAMPP Control Panel → Start MySQL
2. **Start Backend**: 
   ```bash
   cd mandazi-backend
   php artisan serve --port=8001
   ```
3. **Open Frontend**: Double-click `mandazi-frontend/index.html`

## 📋 System Requirements

### ✅ Prerequisites:
- **XAMPP**: MySQL database server
- **PHP 8.1+**: For Laravel backend
- **Composer**: PHP dependency manager
- **Modern Browser**: Chrome, Firefox, Edge

### 📁 File Structure:
```
mandazi/
├── mandazi-backend/          # Laravel API
├── mandazi-frontend/         # HTML/JS Frontend
├── start-mandazi-system.bat  # One-click startup
└── SETUP-GUIDE.md           # This file
```

## 🔧 Configuration

### Backend (Laravel):
- **Port**: 8001 (http://127.0.0.1:8001)
- **Database**: MySQL via XAMPP
- **API Endpoints**: `/api/*`

### Frontend (HTML/JS):
- **Protocol**: file:// (direct HTML opening)
- **API URL**: http://127.0.0.1:8001/api
- **Storage**: localStorage for tokens

## 🧪 Testing the Setup

### 1. Connection Status:
- **Green Badge**: ✅ Backend Connected
- **Yellow Badge**: ⚠️ Backend Offline

### 2. Test Login:
```
Buyer Account:
- Username: johnbuyer
- Password: password123

Seller Account:
- Username: janeseller  
- Password: password123
```

### 3. Test Features:
- **Login**: Username or email works
- **Buyer**: Place orders, STK push payment
- **Seller**: Set prices, view orders

## 🛠️ Troubleshooting

### ❌ "Backend Offline" Error:
1. **Check XAMPP**: MySQL must be running
2. **Check Laravel**: Run `php artisan serve --port=8001`
3. **Check Port**: Ensure port 8001 is free
4. **Check Database**: Verify connection in `.env`

### ❌ "CORS Error":
- **Solution**: Laravel handles CORS automatically
- **Check**: Ensure API URL is correct in config.js

### ❌ "Database Connection Error":
1. **Start XAMPP**: MySQL service must be running
2. **Check .env**: Database credentials must match XAMPP
3. **Run Migrations**: `php artisan migrate`

### ❌ "Token Expired":
- **Solution**: Logout and login again
- **Auto-fix**: System redirects to login automatically

## 📱 M-Pesa Integration

### For Live M-Pesa:
1. **Update .env**: Add real Daraja API credentials
2. **Update ngrok**: Point to port 8001
   ```bash
   ngrok http 8001
   ```
3. **Update callback**: Use ngrok URL in .env

### For Testing:
- **Simulation Mode**: Works automatically
- **No M-Pesa Account**: Uses realistic simulation
- **Status Updates**: Real-time payment status changes

## 🎯 Demo Mode

### For Presentations:
1. **Use**: `demo-login.html` (cache-free)
2. **Quick Login**: One-click demo buttons
3. **No Cache Issues**: All scripts inline

## 📞 Support

### Common Issues:
- **Port 8001 in use**: Change port in Laravel and config.js
- **XAMPP not starting**: Check Windows services
- **Browser cache**: Use Ctrl+F5 or incognito mode

### File Locations:
- **Config**: `mandazi-frontend/js/config.js`
- **Database**: `mandazi-backend/.env`
- **Logs**: `mandazi-backend/storage/logs/`

---

**🎉 Ready to go! Double-click `start-mandazi-system.bat` to begin!**