
FROM php:8.4-apache

# 1. Install ekstensi sistem dan library PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 2. Konfigurasi Apache
RUN a2enmod rewrite \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* \
              /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 3. Atur Document Root ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/sites-available/*.conf

RUN sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# 4. Install Composer resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Salin kode proyek ke container
WORKDIR /var/www/html
COPY . .

# 6. Install dependensi Composer untuk produksi
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# 7. Atur hak akses folder Laravel
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache \
    && chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# 8. Expose port
EXPOSE 80 8080

# 9. Salin konfigurasi Supervisor
RUN mkdir -p /var/log/supervisor && cp supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# 10. Startup container
# Migrasi dijalankan sebelum Supervisor mengelola Apache dan queue worker.
CMD ["sh", "-c", "set -e && \
    rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* && \
    export PORT=\"${PORT:-8080}\" && \
    sed -i \"s/Listen .*/Listen $PORT/\" /etc/apache2/ports.conf && \
    sed -i \"s/<VirtualHost \\*:.*>/<VirtualHost *:$PORT>/\" /etc/apache2/sites-available/000-default.conf && \
    php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear && \
    php artisan migrate --force && \
    php artisan db:seed --class=DemoAccountSeeder --force && \
    exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf"]
