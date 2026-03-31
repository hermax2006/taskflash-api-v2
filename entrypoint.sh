#!/bin/sh
set -e

echo "[entrypoint] Starting container..."

# --- Laravel bootstrap ---
echo "[entrypoint] Clearing config and route caches..."
php artisan config:clear
php artisan route:clear

echo "[entrypoint] Running database migrations..."
php artisan migrate --force

# --- PHP-FPM (foreground, background via & so we can also run Nginx) ---
echo "[entrypoint] Starting PHP-FPM..."
php-fpm --nodaemonize &
PHP_FPM_PID=$!
echo "[entrypoint] PHP-FPM started (PID $PHP_FPM_PID)"

# Give PHP-FPM a moment to bind to its socket/port before Nginx starts
sleep 1

# Verify PHP-FPM is still running before we hand off to Nginx
if ! kill -0 "$PHP_FPM_PID" 2>/dev/null; then
    echo "[entrypoint] ERROR: PHP-FPM failed to start. Aborting." >&2
    exit 1
fi

# --- Nginx (foreground — this becomes the main blocking process) ---
echo "[entrypoint] Starting Nginx..."
exec nginx -g "daemon off;"
