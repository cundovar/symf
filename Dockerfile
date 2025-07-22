# syntax=docker/dockerfile:1

FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev nodejs npm \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony CLI (optionnel)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html
COPY . .
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
RUN composer install --no-dev --optimize-autoloader

# Compile les assets avec AssetMapper
RUN php bin/console asset-map:compile

EXPOSE 10000
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
