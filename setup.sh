#!/bin/bash
set -e

# =============================================================
#  BISS Laravel - One Command Setup
#  Usage: bash setup.sh
# =============================================================

echo "============================================"
echo "  BISS Laravel - Auto Setup"
echo "============================================"

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

# ---------- Colors ----------
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

step() { echo -e "\n${GREEN}[✓] $1${NC}"; }
warn() { echo -e "${YELLOW}[!] $1${NC}"; }
fail() { echo -e "${RED}[✗] $1${NC}"; exit 1; }

# ---------- 1. Fix PHP path (Codespaces ships broken PHP) ----------
step "Checking PHP..."
if ! /usr/bin/php --version &>/dev/null; then
    warn "System PHP not working, installing PHP 8.3..."
    sudo apt-get update -qq 2>/dev/null || true
    sudo add-apt-repository -y ppa:ondrej/php 2>/dev/null || true
    sudo apt-get update -qq 2>/dev/null || true
    sudo apt-get install -y php8.3 php8.3-cli php8.3-mysql php8.3-mbstring \
        php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl \
        2>/dev/null || fail "Failed to install PHP"
    sudo update-alternatives --set php /usr/bin/php8.3 2>/dev/null || true
fi

# Make sure /usr/bin/php is used (not the broken Codespaces one)
export PATH="/usr/bin:$PATH"
PHP_BIN=$(which php)
echo "  Using: $PHP_BIN ($(php --version | head -1))"

# ---------- 2. Install MySQL if needed ----------
step "Checking MySQL..."
if ! command -v mysqld &>/dev/null; then
    warn "MySQL not installed, installing..."
    sudo apt-get install -y mysql-server 2>/dev/null || \
    sudo apt-get install -y --allow-unauthenticated mysql-server 2>/dev/null || \
    fail "Failed to install MySQL"
fi

# Check if MySQL is running
if ! mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then
    fail "MySQL failed to start"
fi
echo "  MySQL running on port 3306"

# ---------- 4. Setup database ----------
step "Setting up database..."
# Allow root login without password via TCP
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';" 2>/dev/null || true
sudo mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true

mysql -u root -h 127.0.0.1 -e "CREATE DATABASE IF NOT EXISTS biss_laravel;" 2>/dev/null

# Import SQL dump if it exists and database is empty
TABLE_COUNT=$(mysql -u root -h 127.0.0.1 -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='biss_laravel';" 2>/dev/null)
SQL_FILE="$PROJECT_DIR/ssotoght_db_biss (1).sql"

if [ "$TABLE_COUNT" -lt "5" ] && [ -f "$SQL_FILE" ]; then
    warn "Importing SQL dump..."
    mysql -u root -h 127.0.0.1 -e "DROP DATABASE IF EXISTS biss_laravel; CREATE DATABASE biss_laravel;"
    mysql -u root -h 127.0.0.1 biss_laravel < "$SQL_FILE"
    echo "  SQL dump imported successfully"
elif [ "$TABLE_COUNT" -lt "5" ]; then
    warn "No SQL dump found, running Laravel migrations..."
    php artisan migrate --force
fi

# Ensure sessions table exists (not always in SQL dump)
mysql -u root -h 127.0.0.1 biss_laravel -e "
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
" 2>/dev/null

# Ensure cache table exists
mysql -u root -h 127.0.0.1 biss_laravel -e "
CREATE TABLE IF NOT EXISTS cache (
    \`key\` VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
" 2>/dev/null

mysql -u root -h 127.0.0.1 biss_laravel -e "
CREATE TABLE IF NOT EXISTS cache_locks (
    \`key\` VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
" 2>/dev/null

echo "  Database: biss_laravel ($(mysql -u root -h 127.0.0.1 -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='biss_laravel';" 2>/dev/null) tables)"

# ---------- 5. Composer install ----------
step "Installing PHP dependencies..."
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist 2>&1 | tail -5
else
    echo "  vendor/ already exists, skipping"
fi

# ---------- 6. Setup .env ----------
step "Setting up environment..."
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Ensure MySQL config in .env
if ! grep -q "DB_CONNECTION=mysql" .env; then
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i 's/# DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i 's/# DB_PORT=.*/DB_PORT=3306/' .env
    sed -i 's/# DB_DATABASE=.*/DB_DATABASE=biss_laravel/' .env
    sed -i 's/# DB_USERNAME=.*/DB_USERNAME=root/' .env
    sed -i 's/# DB_PASSWORD=.*/DB_PASSWORD=/' .env
fi

# Ensure key values are set
grep -q "DB_CONNECTION=mysql" .env || echo "DB_CONNECTION=mysql" >> .env
grep -q "DB_HOST=" .env || echo "DB_HOST=127.0.0.1" >> .env
grep -q "DB_PORT=" .env || echo "DB_PORT=3306" >> .env
grep -q "DB_DATABASE=" .env || echo "DB_DATABASE=biss_laravel" >> .env
grep -q "DB_USERNAME=" .env || echo "DB_USERNAME=root" >> .env

# Auto-detect Codespaces URL and set APP_URL
if [ -n "$CODESPACE_NAME" ]; then
    CODESPACE_URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}"
    sed -i "s|APP_URL=.*|APP_URL=${CODESPACE_URL}|" .env
    echo "  APP_URL set to: $CODESPACE_URL"
fi

# Generate app key if missing
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=\s*$" .env; then
    php artisan key:generate --force
    echo "  App key generated"
fi

# ---------- 7. Storage directories ----------
step "Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ---------- 8. NPM install & build ----------
step "Installing frontend dependencies..."
if [ ! -d "node_modules" ]; then
    npm install 2>&1 | tail -3
else
    echo "  node_modules/ already exists, skipping"
fi

if [ ! -d "public/build" ]; then
    npm run build 2>&1 | tail -3
else
    echo "  public/build/ already exists, skipping"
fi

# ---------- 9. Clear caches ----------
step "Clearing caches..."
php artisan config:clear 2>/dev/null
php artisan cache:clear 2>/dev/null
php artisan view:clear 2>/dev/null

# ---------- 10. Start Laravel ----------
step "Starting Laravel server..."

# Kill any existing artisan serve
pkill -f "artisan serve" 2>/dev/null || true
sleep 1

# Start server
nohup php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/server.log 2>&1 &
sleep 2

if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000 | grep -q "200\|302"; then
    step "Server is running!"
else
    warn "Server may still be starting..."
fi

# ---------- Done! ----------
echo ""
echo "============================================"
echo -e "  ${GREEN}BISS Laravel is ready!${NC}"
echo "============================================"
echo ""
echo "  Laravel : http://localhost:8000"
echo "  MySQL   : 127.0.0.1:3306 (root, no password)"
echo ""
if [ -n "$CODESPACE_NAME" ]; then
    echo "  Codespaces URL:"
    echo "  https://${CODESPACE_NAME}-8000.app.github.dev"
    echo ""
    echo "  (Jangan tambahkan :8000 di akhir URL Codespaces!)"
    echo ""
fi
echo "============================================"
