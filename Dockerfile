# syntax=docker/dockerfile:1

FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev nodejs npm \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

COPY . .

# Active les variables Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
ENV APP_ENV=prod

# Prépare le cache Symfony pour éviter les plantages
RUN mkdir -p var/cache var/log && chmod -R 777 var

# Installe les dépendances PHP sans dev
RUN composer install --no-dev --optimize-autoloader

# Compile les assets (nécessite APP_ENV, var/, etc.)
RUN php bin/console asset-map:compile

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
