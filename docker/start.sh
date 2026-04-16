#!/bin/bash

set -e

# Ensure storage directories exist
mkdir -p storage/framework/{views,sessions,cache} \
         storage/logs \
         storage/app/public \
         bootstrap/cache

# Create .env from environment variables if not exists
if [ ! -f ".env" ]; then
    printenv | grep -E '^(APP_|DB_|CACHE_|SESSION_|MAIL_|LOG_|QUEUE_|BROADCAST_|FILESYSTEM_|REDIS_)' | while IFS='=' read -r key value; do
        echo "${key}=\"${value}\""
    done | sort > .env
    echo "Generated .env from environment variables"
fi

# Discover packages (skipped during build)
php artisan package:discover --ansi || true

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force || true
fi

# Optional: import bundled SQL dump when target DB is empty.
# Enable with AUTO_IMPORT_SQL_DUMP=true on first deployment only.
if [ "${AUTO_IMPORT_SQL_DUMP:-false}" = "true" ] && [ -f "ssotoght_db_biss (1).sql" ]; then
    echo "AUTO_IMPORT_SQL_DUMP enabled. Checking current DB contents..."

    PROJECTS_COUNT=$(mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -D "${DB_DATABASE}" -Nse "SELECT COUNT(*) FROM projects;" 2>/dev/null || echo "__ERR__")

    if [ "${PROJECTS_COUNT}" = "__ERR__" ] || [ "${PROJECTS_COUNT}" = "0" ]; then
        echo "Importing SQL dump into ${DB_DATABASE}..."
        mysql --force -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" < "ssotoght_db_biss (1).sql" || true
        echo "SQL import step finished."
    else
        echo "Skipping SQL import because projects table already has data (${PROJECTS_COUNT} rows)."
    fi
fi

# Ensure public storage symlink exists
php artisan storage:link || true

# Run migrations when explicitly enabled
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction || true
fi

# Cache config, routes, views for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Fix permissions AFTER artisan commands (which create files as root)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "Starting nginx + php-fpm..."

# Start services
exec /usr/bin/supervisord -c /etc/supervisord.conf
