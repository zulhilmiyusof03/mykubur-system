FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.4-apache

WORKDIR /var/www/html

# =========================
# System dependencies
# =========================
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev

# =========================
# PHP extensions (ALL TOGETHER)
# =========================
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql

# =========================
# Apache config
# =========================
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# =========================
# Copy project
# =========================
COPY . .

COPY --from=assets /app/public/build ./public/build

# =========================
# Permissions fix
# =========================
RUN mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache

# =========================
# Composer
# =========================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

# =========================
# Port
# =========================
EXPOSE 80

CMD ["apache2-foreground"]
