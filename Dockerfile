FROM php:8.4-apache

# =========================
# Set working directory
# =========================
WORKDIR /var/www/html

# =========================
# Install system dependencies
# =========================
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip

# =========================
# Install PHP extensions
# =========================
RUN docker-php-ext-install pdo pdo_mysql

# =========================
# Enable Apache rewrite (Laravel requirement)
# =========================
RUN a2enmod rewrite

# =========================
# Set Apache document root to /public
# =========================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# =========================
# Copy project files
# =========================
COPY . .

# =========================
# Permissions fix (IMPORTANT)
# =========================
RUN mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage \
    && chmod -R 775 bootstrap/cache

# =========================
# Install Composer
# =========================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# =========================
# Install dependencies
# =========================
RUN composer install --no-dev --optimize-autoloader

# =========================
# Expose port
# =========================
EXPOSE 80

# =========================
# Start Apache
# =========================
CMD ["apache2-foreground"]