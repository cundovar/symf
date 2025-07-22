# syntax=docker/dockerfile:1

FROM php:8.2-fpm

# Installation des dépendances système nécessaires à Symfony + MySQL + Tailwind
RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev nodejs npm \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Dossier de travail
WORKDIR /var/www/html

# Copie des fichiers
COPY . .

# Variables d’environnement
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
ENV APP_ENV=prod

# Prépare les dossiers de cache et logs
RUN mkdir -p var/cache var/log && chmod -R 777 var

# Installe les dépendances Symfony
RUN composer install --no-dev --optimize-autoloader

# Compile Tailwind CSS
RUN php bin/console tailwind:build

# Compile les assets AssetMapper
RUN php bin/console asset-map:compile

# Expose le port
EXPOSE 10000

# Entrypoint personnalisé
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
