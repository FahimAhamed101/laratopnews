#!/bin/bash
echo "Starting Laravel build process..."

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Cache routes and views
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Optimize
php artisan optimize

echo "Build completed successfully!"