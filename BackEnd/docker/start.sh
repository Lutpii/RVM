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

# The steps above run as root; give anything they created back to Apache's user.
chown -R www-data:www-data storage bootstrap/cache

# No cron on the free tier, so run the scheduler (sessions:expire-stale, platform:tick)
# in-process, as www-data like Apache. Run as root it created root-owned file-cache
# folders that Apache could not write, so every throttled API request failed (16 Nov 2026).
su -s /bin/sh -c "cd /var/www/html && php artisan schedule:work" www-data > /dev/null 2>&1 &

exec apache2-foreground
