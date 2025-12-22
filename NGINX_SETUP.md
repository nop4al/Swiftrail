# Swiftrail Nginx Deployment Guide

## Quick Setup

### 1. Install Dependencies
```powershell
cd C:\Users\Naufal Alif\Documents\VSC Things\College Things\Pemrograman Berbasis Platform\Final Proect\Swiftrail
.\setup-nginx.bat
```

### 2. Setup PHP-FPM
Pastikan PHP-FPM running di port 9000:

```powershell
# Jika using PHP from Chocolatey
php-cgi.exe -b 127.0.0.1:9000

# Atau gunakan XAMPP/Laragon
# Start dari aplikasi mereka
```

### 3. Setup Nginx

**Option A: Using Chocolatey**
```powershell
choco install nginx
# Edit: C:\tools\nginx\conf\nginx.conf
# Copy content dari project's nginx.conf
```

**Option B: Manual Install**
1. Download dari nginx.org
2. Extract ke `C:\nginx`
3. Copy `nginx.conf` ke `C:\nginx\conf\`

### 4. Edit Nginx Config

Buka file nginx.conf dan update:
```nginx
# Ubah path ke folder Swiftrail Anda
root "C:/Users/Naufal Alif/Documents/VSC Things/College Things/Pemrograman Berbasis Platform/Final Proect/Swiftrail/public";
```

### 5. Add to Hosts File

Edit: `C:\Windows\System32\drivers\etc\hosts`

Tambahkan:
```
127.0.0.1 swiftrail.local
127.0.0.1 www.swiftrail.local
```

### 6. Start Nginx

**Chocolatey:**
```powershell
# Start
net start nginx

# Stop
net stop nginx

# Reload
cd C:\tools\nginx
.\nginx.exe -s reload
```

**Manual Install:**
```powershell
cd C:\nginx
.\nginx.exe
```

### 7. Verify

Buka browser: http://swiftrail.local

---

## Environment Setup

Update `.env` untuk production:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=http://swiftrail.local

# Database (jika tidak SQLite)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=swiftrail
DB_USERNAME=root
DB_PASSWORD=

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

---

## Common Issues

### 502 Bad Gateway
- Pastikan PHP-FPM running di port 9000
- Check nginx error log: `nginx/logs/error.log`

### Permission Denied
- Run nginx dengan administrator privileges
- Pastikan folder `storage` dan `bootstrap/cache` writable

### CORS Issues
Sudah configured di `config/cors.php`

---

## Production Checklist

- [ ] Update `.env` APP_ENV to `production`
- [ ] Set APP_DEBUG to `false`
- [ ] Run `php artisan cache:clear` dan `php artisan view:cache`
- [ ] Setup SSL certificate (uncomment HTTPS section di nginx.conf)
- [ ] Setup proper error logging
- [ ] Configure database backups
- [ ] Setup monitoring & alerts
- [ ] Test all API endpoints
- [ ] Verify payment gateway integration (Midtrans)

---

## Commands Useful

```powershell
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Check Laravel version
php artisan --version

# Database
php artisan migrate
php artisan migrate:fresh --seed
php artisan tinker
```

---

## Support

Untuk masalah lebih lanjut:
- Check nginx error logs
- Check Laravel logs di `storage/logs/`
- Run `php artisan about` untuk system info
