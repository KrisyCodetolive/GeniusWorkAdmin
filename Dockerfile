# ===========================================
# Dockerfile production — Genius Work Admin
# Laravel 12 + PHP 8.2 + Nginx
# Version simplifiée pour Coolify
# ===========================================

FROM php:8.3-fpm-alpine

# Dépendances système + extensions PHP
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    zip \
    unzip \
    bash \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libxml2-dev \
    oniguruma-dev \
    icu-dev \
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
    && apk del .build-deps \
    && apk add --no-cache libpng libjpeg-turbo freetype libwebp icu-libs libzip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js pour build Vite
RUN apk add --no-cache nodejs npm

WORKDIR /var/www/html

# Copier d'abord les fichiers de dépendances pour le cache Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

COPY package.json package-lock.json ./
RUN npm ci

# Copier le reste de l'application
COPY . .

# Build des assets frontend
RUN npm run build

# Optimisations Laravel
RUN composer dump-autoload --no-dev --optimize \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache

# Configuration Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configuration PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-php.ini

# Configuration Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Script de démarrage
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

ENTRYPOINT ["/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
