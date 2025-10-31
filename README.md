# 🍞 Snacks Management System

A modern web application for managing snacks orders with integrated M-Pesa payments. Built with Laravel backend and vanilla JavaScript frontend.

![Mandazi System](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow?style=flat-square&logo=javascript)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple?style=flat-square&logo=bootstrap)
![M-Pesa](https://img.shields.io/badge/M--Pesa-Integrated-green?style=flat-square)

## ✨ Features

### 🔐 **Authentication**
- Login with **email OR username**
- Role-based access (Buyer/Seller)
- JWT token authentication
- Password visibility toggle

### 👥 **User Roles**

#### 🛒 **Buyers**
- Browse sellers with real-time pricing
- Place orders with quantity selection
- M-Pesa STK push payments
- Real-time order status tracking
- Order history and statistics

#### 🏪 **Sellers**
- Set and manage mandazi prices
- View all customer orders
- Customer analytics
- Sales statistics and reporting
- Real-time order notifications

### 💳 **M-Pesa Integration**
- Real STK push integration
- Secure callback handling
- Payment status tracking
- Simulation mode for testing
- Comprehensive callback testing tools

### 🎨 **Modern UI/UX**
- Responsive design (mobile-first)
- Real-time status updates
- Smooth animations and transitions
- Professional dashboard layouts
- Cache-busting mechanisms

## 🚀 Quick Start

### Prerequisites
- **PHP 8.1+**
- **Composer**
- **MySQL** (via XAMPP recommended)
- **Modern Browser**

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/mandazi-management-system.git
cd mandazi-management-system
```

### 2. Backend Setup
```bash
cd mandazi-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --port=8001
```

### 3. Database Setup
1. Start XAMPP and enable MySQL
2. Create database `mandazi_db`
3. Update `.env` with your database credentials
4. Run migrations: `php artisan migrate`

### 4. Frontend Setup
```bash
# No build process needed - pure HTML/JS!
# Just open mandazi-frontend/index.html in browser
```

### 5. One-Click Setup (Windows)
```bash
# For easy setup, just run:
start-mandazi-system.bat
```

## 🔧 Configuration

### M-Pesa Setup
1. Get credentials from [Safaricom Developer Portal](https://developer.safaricom.co.ke/)
2. Update `.env` file:
```env
MPESA_CONSUMER_KEY=your_key_here
MPESA_CONSUMER_SECRET=your_secret_here
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_CALLBACK_URL=https://your-domain.com/api/mpesa/callback
```

### ngrok Setup (for M-Pesa callbacks)
```bash
# Install ngrok and run:
ngrok http 8001

# Update MPESA_CALLBACK_URL in .env with ngrok URL
```

## 🧪 Testing

### Demo Accounts
```
Buyer Account:
- Username: johnbuyer
- Email: buyer@test.com  
- Password: password123

Seller Account:
- Username: janeseller
- Email: seller@test.com
- Password: password123
```

### M-Pesa Callback Testing
```bash
# Open the callback tester
open mandazi-frontend/test-callback.html

# Or use command line
test-callback.bat
```

### Demo Mode
```bash
# Use cache-free demo page
open mandazi-frontend/demo-login.html
```

## 📁 Project Structure

```
mandazi-management-system/
├── mandazi-backend/          # Laravel API Backend
│   ├── app/
│   │   ├── Http/Controllers/ # API Controllers
│   │   └── Models/          # Eloquent Models
│   ├── database/
│   │   └── migrations/      # Database Migrations
│   ├── routes/
│   │   └── api.php         # API Routes
│   └── .env.example        # Environment Template
├── mandazi-frontend/         # Frontend Application
│   ├── css/
│   │   └── style.css       # Custom Styles
│   ├── js/
│   │   ├── config.js       # API Configuration
│   │   ├── auth.js         # Authentication Logic
│   │   ├── buyer.js        # Buyer Dashboard
│   │   └── seller.js       # Seller Dashboard
│   ├── index.html          # Login Page
│   ├── buyer-dashboard.html # Buyer Interface
│   ├── seller-dashboard.html # Seller Interface
│   ├── demo-login.html     # Cache-free Demo
│   └── test-callback.html  # M-Pesa Testing
├── start-mandazi-system.bat # One-click Setup
├── PRESENTATION-SETUP.bat   # Demo Preparation
└── README.md               # This File
```

## 🛠️ Development

### Backend (Laravel)
```bash
cd mandazi-backend

# Install dependencies
composer install

# Run migrations
php artisan migrate

# Start development server
php artisan serve --port=8001

# View logs
tail -f storage/logs/laravel.log
```

### Frontend (Vanilla JS)
```bash
# No build process required!
# Edit files directly and refresh browser

# For cache-free development:
open mandazi-frontend/demo-login.html
```

### API Endpoints
```
POST /api/login              # Authentication
POST /api/register           # User Registration
GET  /api/sellers            # Get All Sellers
GET  /api/mandazi            # Get Orders
POST /api/mandazi            # Create Order
POST /api/mandazi/{id}/pay   # Process Payment
POST /api/mpesa/callback     # M-Pesa Callback
```

## 🚀 Deployment

### Production Setup
1. **Backend**: Deploy Laravel to your server
2. **Frontend**: Serve static files via web server
3. **Database**: Configure production MySQL
4. **M-Pesa**: Update callback URLs to production domain
5. **SSL**: Enable HTTPS for M-Pesa integration

### Environment Variables
```bash
# Production .env
APP_ENV=production
APP_DEBUG=false
MPESA_ENV=production
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback
```

## 🧪 Testing & Quality

### Features Tested
- ✅ Authentication (email/username)
- ✅ Role-based access control
- ✅ Order management
- ✅ M-Pesa integration
- ✅ Real-time updates
- ✅ Responsive design
- ✅ Error handling

### Testing Tools Included
- M-Pesa callback tester
- Demo mode for presentations
- System status checker
- Network troubleshooter

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Your Name**
- GitHub: [benjangure](https://github.com/benjangure)
- Email:ngurebenjamin5@gmail.com

## 🙏 Acknowledgments

- [Laravel](https://laravel.com/) - Backend Framework
- [Bootstrap](https://getbootstrap.com/) - UI Framework
- [Safaricom Daraja API](https://developer.safaricom.co.ke/) - M-Pesa Integration
- [Font Awesome](https://fontawesome.com/) - Icons

## 📞 Support

If you encounter any issues:

1. Check the [Issues](https://github.com/benjangure/mandazi-management-system/issues) page
2. Run the system status checker: `check-system.bat`
3. Review the troubleshooting guide in `SETUP-GUIDE.md`
4. Create a new issue with detailed information

---

**⭐ Star this repository if you found it helpful!**
