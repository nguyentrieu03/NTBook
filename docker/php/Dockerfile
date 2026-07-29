# ===== STAGE 1: Composer Builder ================================
FROM composer:2 AS composer_builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --prefer-dist

# ===== STAGE 2: Base =============================================
FROM php:8.4-fpm AS base
# Cài đặt các extension cần thiết cho Laravel 
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
#Thiết lập thư mục làm việc 
WORKDIR /var/www/html
# Expose cổng PHP-FPM 
EXPOSE 9000

# ===== STAGE 3: Development ======================================
FROM base AS development
RUN apt-get update && apt-get install -y git curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

# ===== STAGE 4: Production =======================================
FROM base AS production
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY . . 
COPY --from=composer_builder /app/vendor ./vendor
COPY docker/entrypoint.prod.sh /entrypoint.prod.sh
RUN chmod +x /entrypoint.prod.sh \
 && mkdir -p /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
USER www-data
ENTRYPOINT ["/entrypoint.prod.sh"]
CMD ["php-fpm"]