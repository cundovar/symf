# syntax=docker/dockerfile:1

FROM php:8.2-fpm



    # Install dependencies and extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libmcrypt-dev \
    && docker-php-ext-install pdo_mysql intl opcache zip



# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app directory
WORKDIR /var/www/html

# Copy Symfony project
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Set permissions (optionnel si tu as un user spécifique)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Expose port 9000 for php-fpm
EXPOSE 9000

CMD ["php-fpm"]
