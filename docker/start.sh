#!/bin/bash
set -e

# Ensure storage directories exist
mkdir -p storage/framework/{views,sessions,cache} \
         storage/logs \
         storage/app/public \
         bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force --no-interaction 2>/dev/null || true

# Cache config, routes, views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start services
exec /usr/bin/supervisord -c /etc/supervisord.conf
