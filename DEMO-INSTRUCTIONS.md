# 🎯 Mandazi System - Demo Instructions

## 🚀 Quick Demo Setup (No Cache Issues)

### Option 1: Use Demo Login Page (Recommended)
```
Open: mandazi-frontend/demo-login.html
```
- ✅ **Cache-free**: All scripts are inline
- ✅ **Quick login buttons**: One-click demo access
- ✅ **Visual indicators**: Shows it's demo mode
- ✅ **No external dependencies**: Won't break due to cache

### Option 2: Clear Cache Before Demo
1. **Run cache cleaner**: Double-click `clear-cache.bat`
2. **Clear browser cache**: Ctrl+Shift+Delete → Select "All time" → Clear
3. **Use incognito mode**: Ctrl+Shift+N (Chrome) or Ctrl+Shift+P (Firefox)

## 🧪 Demo Flow

### 1. **Login Demo**
```
URL: demo-login.html
Credentials: 
- Buyer: johnbuyer / password123
- Seller: janeseller / password123
```

### 2. **Buyer Demo Flow**
1. Login as buyer → Buyer Dashboard
2. **Place Order**: Select seller → Enter quantity → Submit
3. **STK Push**: Click "Pay Now" → Confirm dialog → Watch status update
4. **Real-time Updates**: Status changes from Pending → Paid

### 3. **Seller Demo Flow**
1. Login as seller → Seller Dashboard  
2. **Set Price**: Update price per unit in price management section
3. **View Orders**: See incoming orders from buyers
4. **Monitor Sales**: Check statistics and customer data

## 🛠️ Troubleshooting Cache Issues

### If Demo Shows Old Behavior:
1. **Hard Refresh**: Ctrl+F5
2. **Clear Cache**: Ctrl+Shift+Delete
3. **Incognito Mode**: Ctrl+Shift+N
4. **Use Demo Page**: `demo-login.html` (cache-free)

### If API Errors Occur:
1. **Check Server**: Ensure Laravel runs on port 8001
2. **Check ngrok**: Ensure forwarding to port 8001
3. **Check Console**: F12 → Console for error details

## 📱 M-Pesa Demo

### STK Push Flow:
1. **Place Order**: As buyer, create new order
2. **Initiate Payment**: Click "Pay Now" button
3. **Confirm Dialog**: Shows phone number confirmation
4. **STK Simulation**: System simulates M-Pesa STK push
5. **Status Update**: Order status updates automatically

### Demo Script:
```
"Let me show you the M-Pesa integration:
1. I'll place an order for 5 mandazis
2. Click Pay Now - see the confirmation dialog
3. The system sends STK push to the phone
4. Watch the status update in real-time
5. The order changes from Pending to Paid automatically"
```

## 🎨 UI Features to Highlight

### Modern Design:
- ✅ Gradient backgrounds and buttons
- ✅ Smooth animations and transitions  
- ✅ Responsive design for mobile
- ✅ Password visibility toggle
- ✅ Real-time notifications

### User Experience:
- ✅ Username OR email login
- ✅ Seller price management
- ✅ Automatic price loading for buyers
- ✅ STK push confirmation dialogs
- ✅ Real-time payment status updates

## 🔧 Technical Highlights

### Backend Features:
- ✅ Laravel 11 with Sanctum authentication
- ✅ M-Pesa Daraja API integration
- ✅ Real STK push with simulation fallback
- ✅ Webhook callback handling
- ✅ Role-based access control

### Frontend Features:
- ✅ Vanilla JavaScript (no framework dependencies)
- ✅ Bootstrap 5 for responsive UI
- ✅ Real-time status polling
- ✅ Cache-busting mechanisms
- ✅ Progressive enhancement

## 📊 Demo Statistics

Show these impressive numbers:
- ⚡ **Real-time updates**: 2-second polling
- 🔒 **Secure authentication**: JWT tokens
- 📱 **M-Pesa integration**: Live STK push
- 🎨 **Modern UI**: CSS3 animations
- 📱 **Mobile responsive**: Works on all devices

---

**Pro Tip**: Always use `demo-login.html` for presentations to avoid any cache-related issues! 🚀