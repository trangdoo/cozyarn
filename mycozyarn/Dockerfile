# syntax=docker/dockerfile:1.6
#
# Multi-stage build cho CozyYarn (Laravel 11 + Vite).
#   1. composer-stage : cài PHP deps (production, no-dev)
#   2. node-stage     : build frontend assets bằng Vite
#   3. runtime        : PHP 8.3 FPM mỏng, copy app + deps + assets vào
#
# Build:    docker build -t cozyarn:latest .
# Run:      docker compose up -d   (xem docker-compose.yml)

ARG PHP_VERSION=8.3
ARG NODE_VERSION=20

# ───────────── 1. Composer stage ─────────────
FROM composer:2 AS composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# ───────────── 2. Node stage ─────────────
FROM node:${NODE_VERSION}-alpine AS node-stage
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ───────────── 3. Runtime ─────────────
FROM php:${PHP_VERSION}-fpm-alpine AS runtime

RUN apk add --no-cache \
        bash \
        icu-dev \
        libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev oniguruma-dev \
        mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        opcache \
        bcmath

WORKDIR /var/www/html

# Copy app + vendor + built assets
COPY --from=composer-stage /app /var/www/html
COPY --from=node-stage    /app/public/build /var/www/html/public/build

# Quyền cho storage + bootstrap/cache
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Opcache cho production
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.jit=1255'; \
        echo 'opcache.jit_buffer_size=64M'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 9000
CMD ["php-fpm"]
