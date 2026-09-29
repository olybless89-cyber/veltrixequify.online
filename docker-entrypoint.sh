#!/bin/sh
set -e

# 1. Dynamic Port Configuration for Railway
export PORT=${PORT:-80}
echo "=========================================================="
echo "    Starting Veltrix Equify Platform on port: ${PORT}     "
echo "=========================================================="

# Substitute PORT into Nginx configuration
if [ -f /etc/nginx/templates/default.conf.template ]; then
    envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf
elif [ -f /etc/nginx/conf.d/default.conf ]; then
    sed -i "s/listen .*/listen ${PORT};/g" /etc/nginx/conf.d/default.conf
fi

# 2. Environment file handling
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        echo "Creating .env from .env.example..."
        cp /var/www/html/.env.example /var/www/html/.env
    fi
fi

# Ensure APP_KEY exists
if ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "Generating Application Key..."
    php /var/www/html/artisan key:generate --force --no-interaction || true
fi

# 3. Ensure storage folders and permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/assets/uploads 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# 4. Ensure public assets link
if [ ! -e /var/www/html/public/assets ]; then
    ln -sf /var/www/html/assets /var/www/html/public/assets
fi

# 5. Database Auto-Provisioning
echo "Running Veltrix Equify Database Initialization check..."
php /var/www/html/artisan matrix:init-db --no-interaction || echo "Notice: Database init step completed or will connect when DB is ready."

# 6. Optimize Caches
php /var/www/html/artisan optimize:clear || true

echo "Veltrix Equify platform ready! Launching web server and scheduler..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
