#!/bin/sh
set -e

# Render tells the container which port to listen on via $PORT.
PORT="${PORT:-10000}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# No cron on the free tier, so run the scheduler (sessions:expire-stale) in-process.
php artisan schedule:work > /dev/null 2>&1 &

exec apache2-foreground
