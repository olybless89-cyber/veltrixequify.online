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
    export MAIL_PORT="${MAIL_PORT:-2465}"  # Railway blocks 465/587; Resend alt SSL port
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

# 6. Application key
# .env is rebuilt from .env.example on every container start (nothing
# persists it), so blindly running key:generate here would mint a brand
# new key on every single deploy -- and Laravel encrypts session/CSRF
# cookies with this key, so every deploy would silently invalidate every
# logged-in session and every open form. If APP_KEY was provided as a
# platform env var (Railway), persist that same value into .env so it
# stays stable across deploys; only fall back to generating a fresh one
# when no key has ever been set anywhere (first boot with none configured).
if [ -n "$APP_KEY" ]; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
elif ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "Generating Application Key..."
    php /var/www/html/artisan key:generate --force --no-interaction || true
    echo "⚠️  No APP_KEY was set as a platform variable -- a new one was generated for this boot only."
    echo "   Set APP_KEY as a persistent env var, or every future deploy will invalidate all sessions."
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

# If a persistent volume is mounted here and this is its first boot, it
# starts empty and shadows the baked-in seed images entirely. Populate it
# once from the pristine backup taken at build time (see Dockerfile) --
# on every later boot the volume already has content (seed images plus
# anything uploaded via the admin panel since), so this is skipped and
# nothing already there is touched.
if [ -z "$(ls -A /var/www/html/assets/uploads 2>/dev/null)" ] && [ -d /var/www/html/assets/uploads-seed ]; then
    echo "Uploads directory is empty -- seeding it from the baked-in defaults..."
    cp -r /var/www/html/assets/uploads-seed/. /var/www/html/assets/uploads/
fi

chown -R www-data:www-data /var/www/html/assets/uploads 2>/dev/null || true
chmod -R 775 /var/www/html/assets/uploads 2>/dev/null || true

# 8. Public assets symlink
if [ ! -e /var/www/html/public/assets ]; then
    ln -sf /var/www/html/assets /var/www/html/public/assets
fi

# 9. Wait for database to be ready (max 60s)
echo "Waiting for database connection..."
DB_READY=0
for i in $(seq 1 30); do
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

# 10b. Re-fix storage/bootstrap-cache ownership one more time.
# Every artisan call above (db:monitor, matrix:init-db, optimize:clear,
# config:cache, route:cache, view:cache) runs as root (this entrypoint
# has no USER directive) and can create brand-new files under
# storage/logs or storage/framework/* -- e.g. the first line ever
# written to storage/logs/laravel.log. Those get created root-owned,
# *after* the one-time chown at step 7 already ran, so php-fpm's
# worker (which drops to www-data) hits "Permission denied" the first
# time it tries to append to that file -- which is exactly what
# masked the real error behind every request that first triggers a
# framework log write. Re-running chown here, right before supervisor
# starts serving requests, covers anything created in between.
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo ""
echo "🚀 Veltrix Equify is ready! Launching services..."
echo ""

# 11. Start Supervisor (nginx + php-fpm + scheduler)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
