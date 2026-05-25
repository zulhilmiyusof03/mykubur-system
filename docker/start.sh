#!/usr/bin/env bash
set -e

PORT="${PORT:-8080}"

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

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
