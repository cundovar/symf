# syntax=docker/dockerfile:1

FROM php:8.2-fpm

# Install dépendance et extension
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libmcrypt-dev \
    curl \
    gnupg \
    && docker-php-ext-install pdo_mysql intl opcache zip

# Install Node.js et npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app directory
WORKDIR /var/www/html

# Copy Symfony project
COPY . .

# Install PHP dependences
RUN composer install --no-interaction --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Expose port 9000 for php-fpm
EXPOSE 9000

CMD ["php-fpm"]
