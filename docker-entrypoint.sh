#!/bin/sh
set -e

# ─────────────────────────────────────────────────────────────
#   Veltrix Equify — Railway Auto-Deploy Entrypoint
# ─────────────────────────────────────────────────────────────

# 1. Dynamic Port for Railway
export PORT=${PORT:-80}
echo "=========================================================="
echo "   Starting Veltrix Equify Platform on port: ${PORT}      "
echo "=========================================================="

# 2. Build Nginx config from template
if [ -f /etc/nginx/templates/default.conf.template ]; then
    envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf
elif [ -f /etc/nginx/conf.d/default.conf ]; then
    sed -i "s/listen .*/listen ${PORT};/g" /etc/nginx/conf.d/default.conf
fi

# 3. Map Railway-injected MySQL env vars → Laravel DB vars
# Railway injects: MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
# Also supports: DATABASE_URL (for Railway MySQL plugin)
if [ -n "$MYSQLHOST" ] && [ -z "$DB_HOST" ]; then
    export DB_HOST="$MYSQLHOST"
fi
if [ -n "$MYSQLPORT" ] && [ -z "$DB_PORT" ]; then
    export DB_PORT="$MYSQLPORT"
fi
if [ -n "$MYSQLUSER" ] && [ -z "$DB_USERNAME" ]; then
    export DB_USERNAME="$MYSQLUSER"
fi
if [ -n "$MYSQLPASSWORD" ] && [ -z "$DB_PASSWORD" ]; then
    export DB_PASSWORD="$MYSQLPASSWORD"
fi
if [ -n "$MYSQLDATABASE" ] && [ -z "$DB_DATABASE" ]; then
    export DB_DATABASE="$MYSQLDATABASE"
fi

# 4. Map Resend API key → MAIL_PASSWORD automatically
if [ -n "$RESEND_API_KEY" ]; then
    export MAIL_PASSWORD="$RESEND_API_KEY"
    export MAIL_MAILER="${MAIL_MAILER:-smtp}"
    export MAIL_HOST="${MAIL_HOST:-smtp.resend.com}"
    export MAIL_PORT="${MAIL_PORT:-465}"
    export MAIL_USERNAME="${MAIL_USERNAME:-resend}"
    export MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-ssl}"
    echo "✅ Resend email configured."
fi

# 5. Create .env if missing
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Inject Railway DB values into .env file
if [ -n "$DB_HOST" ]; then
    sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" /var/www/html/.env
fi
if [ -n "$DB_PORT" ]; then
    sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" /var/www/html/.env
fi
if [ -n "$DB_USERNAME" ]; then
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME}|" /var/www/html/.env
fi
if [ -n "$DB_PASSWORD" ]; then
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" /var/www/html/.env
fi
if [ -n "$DB_DATABASE" ]; then
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_DATABASE}|" /var/www/html/.env
fi
if [ -n "$APP_URL" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" /var/www/html/.env
fi
if [ -n "$RESEND_API_KEY" ]; then
    sed -i "s|^RESEND_API_KEY=.*|RESEND_API_KEY=${RESEND_API_KEY}|" /var/www/html/.env
    sed -i "s|^MAIL_PASSWORD=.*|MAIL_PASSWORD=${RESEND_API_KEY}|" /var/www/html/.env
fi

# 6. Generate APP_KEY if missing
if ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "Generating Application Key..."
    php /var/www/html/artisan key:generate --force --no-interaction || true
fi

# 7. Storage directories & permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Fix uploads directory
mkdir -p /var/www/html/assets/uploads
chown -R www-data:www-data /var/www/html/assets/uploads 2>/dev/null || true

# 8. Public assets symlink
if [ ! -e /var/www/html/public/assets ]; then
    ln -sf /var/www/html/assets /var/www/html/public/assets
fi

# 9. Wait for database to be ready (max 60s)
echo "Waiting for database connection..."
DB_READY=0
for i in $(seq 1 30); do
    if php /var/www/html/artisan db:monitor --databases=mysql --max=1 --no-interaction 2>/dev/null; then
        DB_READY=1
        break
    fi
    # Fallback check
    if php -r "
        try {
            \$pdo = new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            echo 'ok';
        } catch(Exception \$e) { echo 'fail'; }
    " 2>/dev/null | grep -q "ok"; then
        DB_READY=1
        break
    fi
    echo "  DB not ready yet (attempt $i/30)... retrying in 2s"
    sleep 2
done

if [ "$DB_READY" = "1" ]; then
    echo "✅ Database connected. Running initialization..."
    # Run Veltrix custom init (imports SQL if fresh, seeds admin, etc.)
    php /var/www/html/artisan matrix:init-db --no-interaction || echo "Notice: DB init already completed."
else
    echo "⚠️  DB connection timeout — will retry at request time."
fi

# 10. Cache optimization
php /var/www/html/artisan optimize:clear 2>/dev/null || true
php /var/www/html/artisan config:cache 2>/dev/null || true
php /var/www/html/artisan route:cache 2>/dev/null || true
php /var/www/html/artisan view:cache 2>/dev/null || true

echo ""
echo "🚀 Veltrix Equify is ready! Launching services..."
echo ""

# 11. Start Supervisor (nginx + php-fpm + scheduler)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
