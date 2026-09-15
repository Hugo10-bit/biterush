#!/bin/bash
set -e

# Adapt Apache port to Cloud dynamic $PORT (Railway/Render)
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf 2>/dev/null || echo "Listen $PORT" > /etc/apache2/ports.conf
    sed -i "s/:80>/:$PORT>/g" /etc/apache2/sites-available/000-default.conf
fi

# Ensure storage directory structure and permissions
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink
php artisan storage:link --force || true

# Optimize caches in production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Automatically run database migrations and seeder if database is connected
if [ -n "$DB_HOST" ] || [ -n "$DB_URL" ]; then
    echo "Checking database connection and running migrations..."
    php artisan migrate --force || echo "Migration skipped or database not ready yet."
    echo "Seeding initial admin and sample data if needed..."
    php artisan db:seed --force || echo "Seeding completed or skipped."
fi

# Ensure only mpm_prefork is enabled in mods-enabled
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* 2>/dev/null || true
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load 2>/dev/null || true
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf 2>/dev/null || true

echo "Starting Apache web server..."
exec apache2-foreground