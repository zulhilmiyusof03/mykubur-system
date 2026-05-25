#!/usr/bin/env bash
set -e

if [ -n "${PORT:-}" ]; then
    sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf
fi

php artisan storage:link || true
php artisan config:cache
php artisan view:cache

for attempt in {1..10}; do
    if php artisan migrate --force; then
        break
    fi

    if [ "$attempt" -eq 10 ]; then
        echo "Database migration failed after ${attempt} attempts."
        exit 1
    fi

    echo "Database is not ready yet. Retrying migration in 5 seconds..."
    sleep 5
done

exec apache2-foreground
