#!/bin/sh
set -e

export PORT="${PORT:-8000}"

if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] ERROR: APP_KEY is not set. Add it to your Koyeb environment variables." >&2
    exit 1
fi

echo "[entrypoint] Generating nginx config on port ${PORT}..."
envsubst '$PORT' < /var/www/html/docker/nginx.conf.template > /etc/nginx/conf.d/default.conf
rm -f /etc/nginx/sites-enabled/default

mkdir -p \
    /var/www/html/storage/logs \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "[entrypoint] Running database migrations..."
attempt=1
until php artisan migrate --force; do
    if [ "$attempt" -ge 10 ]; then
        echo "[entrypoint] ERROR: migrations failed after ${attempt} attempts" >&2
        exit 1
    fi
    attempt=$((attempt + 1))
    echo "[entrypoint] Migration failed, retrying (${attempt}/10)..."
    sleep 3
done

echo "[entrypoint] Caching configuration and routes..."
php artisan config:cache
php artisan route:cache

echo "[entrypoint] Starting supervisor (php-fpm, reverb, nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
