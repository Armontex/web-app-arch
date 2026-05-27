#!/bin/sh
set -e

if [ "${RUN_LARAVEL_BOOTSTRAP:-true}" = "true" ]; then
    php artisan migrate --force

    if ! php -r '$pdo = new PDO("mysql:host=".getenv("DB_HOST").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit($pdo->query("SHOW TABLES LIKE '\''oauth_clients'\''")->fetch() ? 0 : 1);'; then
        php artisan passport:install --force
    fi
fi

exec "$@"
