# 🚀 Deployment Guide

## 📋 Pre-Deployment Checklist

### ✅ **Environment Setup**
- [ ] Production server with PHP 8.1+
- [ ] MySQL database configured
- [ ] SSL certificate installed
- [ ] Domain name configured
- [ ] M-Pesa production credentials obtained

### ✅ **Code Preparation**
- [ ] All features tested locally
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] Static assets optimized

## 🌐 Production Deployment

### 1. **Server Setup**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring nginx mysql-server composer git -y

# Configure PHP
sudo systemctl enable php8.2-fpm
sudo systemctl start php8.2-fpm
```

### 2. **Deploy Backend**
```bash
# Clone repository
git clone https://github.com/yourusername/mandazi-management-system.git
cd mandazi-management-system/mandazi-backend

# Install dependencies
composer install --optimize-autoloader --no-dev

# Configure environment
cp .env.example .env
nano .env  # Update production settings

# Generate key and migrate
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. **Deploy Frontend**
```bash
# Copy frontend files to web root
sudo cp -r ../mandazi-frontend/* /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
```

### 4. **Configure Nginx**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html;
    index index.html;

    # Frontend routes
    location / {
        try_files $uri $uri/ /index.html;
    }

    # API routes
    location /api {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### 5. **SSL Configuration**
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 🔧 Production Configuration

### Environment Variables (.env)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_DATABASE=mandazi_production
DB_USERNAME=mandazi_user
DB_PASSWORD=secure_password

MPESA_ENV=production
MPESA_CALLBACK_URL=https://yourdomain.com/api/mpesa/callback
```

### Frontend Configuration
Update `mandazi-frontend/js/config.js`:
```javascript
const config = {
    apiUrl: 'https://yourdomain.com/api',
    // ... rest of config
};
```

## 📊 Monitoring & Maintenance

### Log Monitoring
```bash
# Laravel logs
tail -f /path/to/mandazi-backend/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### Database Backup
```bash
# Daily backup script
#!/bin/bash
mysqldump -u mandazi_user -p mandazi_production > backup_$(date +%Y%m%d).sql
```

### Health Checks
```bash
# API health check
curl https://yourdomain.com/api/test-simple

# Database connection check
php artisan tinker --execute="DB::connection()->getPdo();"
```

## 🔒 Security Considerations

### ✅ **Implemented Security**
- JWT token authentication
- CORS protection
- SQL injection prevention (Eloquent ORM)
- XSS protection
- CSRF protection
- Input validation

### ✅ **Additional Recommendations**
- Rate limiting on API endpoints
- IP whitelisting for admin functions
- Regular security updates
- Database encryption at rest
- Secure M-Pesa credential storage

## 🚨 Troubleshooting

### Common Issues

#### **500 Internal Server Error**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### **Database Connection Error**
```bash
# Test connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check credentials in .env
```

#### **M-Pesa Callback Issues**
```bash
# Check ngrok/domain configuration
# Verify SSL certificate
# Test callback endpoint manually
```

## 📈 Performance Optimization

### Backend Optimization
```bash
# Enable OPcache
# Configure Redis for caching
# Optimize database queries
# Use queue workers for heavy tasks
```

### Frontend Optimization
```bash
# Minify CSS/JS files
# Enable gzip compression
# Use CDN for static assets
# Implement service workers
```

---

**🎯 Ready for production deployment!**