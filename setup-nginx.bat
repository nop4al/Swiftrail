@echo off
REM Swiftrail Production Setup Script for Nginx

echo.
echo ========================================
echo  Swiftrail Production Setup
echo ========================================
echo.

REM Install PHP dependencies
echo [1/7] Installing PHP dependencies...
call composer install --optimize-autoloader --no-dev
if %errorlevel% neq 0 (
    echo Error: Composer install failed
    exit /b 1
)

REM Setup environment
echo [2/7] Setting up environment...
if not exist .env (
    copy .env.example .env
    echo Created .env file
)

REM Generate application key
echo [3/7] Generating application key...
php artisan key:generate --force

REM Install Node dependencies
echo [4/7] Installing Node dependencies...
call npm install
if %errorlevel% neq 0 (
    echo Error: npm install failed
    exit /b 1
)

REM Build assets
echo [5/7] Building assets...
call npm run build
if %errorlevel% neq 0 (
    echo Error: npm run build failed
    exit /b 1
)

REM Run migrations
echo [6/7] Running database migrations...
php artisan migrate --force

REM Clear caches
echo [7/7] Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan view:cache

REM Set permissions (Windows)
echo.
echo ========================================
echo  Setup Complete!
echo ========================================
echo.
echo Next steps:
echo.
echo 1. Make sure PHP-FPM is running on 127.0.0.1:9000
echo    (Or adjust nginx.conf fastcgi_pass setting)
echo.
echo 2. Copy nginx.conf to your nginx config directory:
echo    - For Chocolatey: C:\tools\nginx\conf\nginx.conf
echo    - Or your custom nginx installation path
echo.
echo 3. Edit the 'root' path in nginx.conf to match your Swiftrail path
echo.
echo 4. Start nginx:
echo    nginx.exe -s start
echo.
echo 5. Add to your hosts file (C:\Windows\System32\drivers\etc\hosts):
echo    127.0.0.1 swiftrail.local www.swiftrail.local
echo.
echo 6. Access your app at: http://swiftrail.local
echo.
pause
