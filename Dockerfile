FROM php:8.1-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    gettext \
    icu-dev \
    mysql-client

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) calendar \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
        intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy configuration files
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application source code
COPY . /var/www/html

# Keep a pristine backup of the baked-in uploads (seed images that ship
# with the app) at a separate path, outside where a Railway volume gets
# mounted at runtime. A volume mounted at assets/uploads starts empty and
# would otherwise shadow these files entirely -- docker-entrypoint.sh uses
# this backup to seed the volume on its first boot, and leaves it alone
# on every boot after that so admin-uploaded images are never touched.
RUN cp -r /var/www/html/assets/uploads /var/www/html/assets/uploads-seed

# Make entrypoint script executable
RUN chmod +x /var/www/html/docker-entrypoint.sh

# Create required directories and set permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
             /etc/nginx/http.d \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/assets/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run composer install if vendor is missing, or dump optimized autoload if present
RUN if [ ! -d vendor ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-reqs; \
    else \
        composer dump-autoload --optimize --no-dev || true; \
    fi

# Dynamic PORT support for Railway
EXPOSE 80 8080 3000

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]
