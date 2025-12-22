#!/bin/bash
set -e

echo "Deploying Laravel app..."

# Clean up any stale cache
rm -rf bootstrap/cache/routes-v7.php bootstrap/cache/packages.php bootstrap/cache/services.php 2>/dev/null || true

# Install dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-scripts

# Generate APP_KEY if not exists
if [ -z "$APP_KEY" ]; then
  echo "Generating APP_KEY..."
  php artisan key:generate
fi

# Clear old caches
echo "Clearing old caches..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Generate config cache
echo "Caching configuration..."
php artisan config:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force 2>/dev/null || echo "Warning: Migrations failed or already run"

# Generate other caches only if migration succeeded
echo "Caching routes and views..."
php artisan route:cache 2>/dev/null || echo "Warning: Route cache failed, running without cache"
php artisan view:cache 2>/dev/null || echo "Warning: View cache failed"

# Build frontend (if using Vite)
echo "Building frontend..."
npm install --legacy-peer-deps
npm run build

echo "Deployment completed!"
