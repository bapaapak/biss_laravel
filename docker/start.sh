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
# You can force the file name via SQL_DUMP_FILE.
SQL_DUMP_FILE="${SQL_DUMP_FILE:-}"
if [ -z "${SQL_DUMP_FILE}" ]; then
    if [ -f "ssotoght_db_biss (1) (2).sql" ]; then
        SQL_DUMP_FILE="ssotoght_db_biss (1) (2).sql"
    elif [ -f "ssotoght_db_biss (1).sql" ]; then
        SQL_DUMP_FILE="ssotoght_db_biss (1).sql"
    fi
fi

if [ "${AUTO_IMPORT_SQL_DUMP:-false}" = "true" ] && [ -n "${SQL_DUMP_FILE}" ] && [ -f "${SQL_DUMP_FILE}" ]; then
    echo "AUTO_IMPORT_SQL_DUMP enabled. Checking current DB contents..."
    echo "Using SQL dump file: ${SQL_DUMP_FILE}"

    # Ensure target database exists before count/import.
    php -r '
        $h = getenv("DB_HOST");
        $p = getenv("DB_PORT") ?: "3306";
        $d = getenv("DB_DATABASE");
        $u = getenv("DB_USERNAME");
        $pw = getenv("DB_PASSWORD");
        try {
            $pdo = new PDO("mysql:host={$h};port={$p};charset=utf8mb4", $u, $pw, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $safeDb = str_replace("`", "``", $d);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Ensured database exists: {$d}";
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }
    ' || true

    PROJECTS_COUNT=$(php -r '
        $h = getenv("DB_HOST");
        $p = getenv("DB_PORT") ?: "3306";
        $d = getenv("DB_DATABASE");
        $u = getenv("DB_USERNAME");
        $pw = getenv("DB_PASSWORD");
        try {
            $pdo = new PDO("mysql:host={$h};port={$p};dbname={$d};charset=utf8mb4", $u, $pw, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
            ]);
            $count = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
            echo $count === false ? "0" : $count;
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }
    ' 2>/dev/null || echo "__ERR__")

    if [ "${PROJECTS_COUNT}" = "__ERR__" ] || [ "${PROJECTS_COUNT}" = "0" ]; then
        echo "Importing SQL dump into ${DB_DATABASE}..."
        if php -r '
            $h = getenv("DB_HOST");
            $p = getenv("DB_PORT") ?: "3306";
            $d = getenv("DB_DATABASE");
            $u = getenv("DB_USERNAME");
            $pw = getenv("DB_PASSWORD");
            $file = getenv("SQL_DUMP_FILE");

            try {
                $pdo = new PDO("mysql:host={$h};port={$p};dbname={$d};charset=utf8mb4", $u, $pw, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
                ]);

                $sql = @file($file, FILE_IGNORE_NEW_LINES);
                if ($sql === false) {
                    throw new RuntimeException("Cannot read SQL dump file: {$file}");
                }

                $buffer = "";
                $ok = 0;
                $fail = 0;

                foreach ($sql as $line) {
                    $trim = trim($line);

                    // Skip full-line comments and empty lines.
                    if ($trim === "" || str_starts_with($trim, "--") || str_starts_with($trim, "/*") || str_starts_with($trim, "*/") || str_starts_with($trim, "/*!")) {
                        continue;
                    }

                    $buffer .= $line . "\n";

                    if (str_ends_with($trim, ";")) {
                        try {
                            $pdo->exec($buffer);
                            $ok++;
                        } catch (Throwable $e) {
                            $fail++;
                        }
                        $buffer = "";
                    }
                }

                if (trim($buffer) !== "") {
                    try {
                        $pdo->exec($buffer);
                        $ok++;
                    } catch (Throwable $e) {
                        $fail++;
                    }
                }

                echo "SQL import step finished. statements_ok={$ok}, statements_failed={$fail}";
            } catch (Throwable $e) {
                fwrite(STDERR, $e->getMessage());
                exit(1);
            }
        '; then
            echo "SQL import step finished."
        else
            echo "SQL import failed."
        fi

        COUNTS_AFTER=$(php -r '
            $h = getenv("DB_HOST");
            $p = getenv("DB_PORT") ?: "3306";
            $d = getenv("DB_DATABASE");
            $u = getenv("DB_USERNAME");
            $pw = getenv("DB_PASSWORD");
            try {
                $pdo = new PDO("mysql:host={$h};port={$p};dbname={$d};charset=utf8mb4", $u, $pw, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
                $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                $budgetPlans = $pdo->query("SELECT COUNT(*) FROM budget_plans")->fetchColumn();
                $prs = $pdo->query("SELECT COUNT(*) FROM purchase_requests")->fetchColumn();
                echo "projects={$projects}, users={$users}, budget_plans={$budgetPlans}, purchase_requests={$prs}";
            } catch (Throwable $e) {
                echo "projects=__ERR__, users=__ERR__, budget_plans=__ERR__, purchase_requests=__ERR__";
            }
        ' 2>/dev/null)

        echo "Post-import row counts: ${COUNTS_AFTER}"
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
