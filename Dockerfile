# syntax=docker/dockerfile:1

FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev nodejs npm \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Symfony CLI (optionnel)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Répertoire de travail
WORKDIR /var/www/html

# Copie du code source
COPY . .

# Variables d’environnement
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
ENV APP_ENV=prod

# Crée les dossiers de cache + logs
RUN mkdir -p var/cache var/log && chmod -R 777 var

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Applique les migrations Doctrine
RUN php bin/console doctrine:migrations:migrate --no-interaction

# Compile Tailwind
RUN php bin/console tailwind:build

# Compile les assets (AssetMapper)
RUN php bin/console asset-map:compile

# Port exposé
EXPOSE 10000

# Serveur PHP interne (à adapter en prod)
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
CMD ["/entrypoint.sh"]
