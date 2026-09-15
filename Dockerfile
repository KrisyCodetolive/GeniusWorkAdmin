# ===========================================
# Dockerfile production — Genius Work Admin
# Laravel 12 + PHP-FPM 8.2 + Nginx
# ===========================================

FROM php:8.2-fpm-alpine AS base

# Dépendances système
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    curl \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-libs \
    icu-dev \
    bash \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        bcmath \
        opcache \
        pcntl \
        intl \
        mbstring \
        xml \
    && apk del --no-cache \
        freetype-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libxml2-dev \
        oniguruma-dev \
        icu-dev

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ===========================================
# Étape 1 : dépendances PHP
# ===========================================
FROM base AS composer-deps

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# ===========================================
# Étape 2 : dépendances frontend + build
# ===========================================
FROM node:22-alpine AS frontend-deps

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ===========================================
# Étape 3 : image finale
# ===========================================
FROM base AS production

# Copier l'application
COPY . .

# Copier les dépendances Composer
COPY --from=composer-deps /var/www/html/vendor ./vendor

# Copier les assets buildés
COPY --from=frontend-deps /app/public/build ./public/build

# Optimisations Laravel
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache

# Configuration Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configuration PHP-FPM
COPY docker/php-fpm.conf /usr/local/etc/php/conf.d/zz-custom.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-php.ini

# Configuration Supervisor (Nginx + PHP-FPM + Queue)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de démarrage
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
