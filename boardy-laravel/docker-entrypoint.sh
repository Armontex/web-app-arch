#!/bin/sh
set -e

if [ "${RUN_LARAVEL_BOOTSTRAP:-true}" = "true" ]; then
    if [ -d vendor/laravel/passport/database/migrations ] && [ ! -f database/migrations/2016_06_01_000004_create_oauth_clients_table.php ]; then
        cp vendor/laravel/passport/database/migrations/*.php database/migrations/
    fi

    php artisan migrate --force

    if ! php -r '$pdo = new PDO("mysql:host=".getenv("DB_HOST").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit($pdo->query("SHOW TABLES LIKE '\''oauth_clients'\''")->fetch() ? 0 : 1);'; then
        php artisan passport:install --force
    fi
fi

exec "$@"
