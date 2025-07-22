# syntax=docker/dockerfile:1

# Utilise PHP 8.2 avec FPM
FROM php:8.2-fpm

# Installe les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    zip unzip git curl libicu-dev libonig-dev libxml2-dev \
    libzip-dev libpq-dev nodejs npm \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installe la CLI Symfony (optionnel mais pratique)
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Définit le répertoire de travail
WORKDIR /var/www/html

# Copie tous les fichiers du projet
COPY . .

# Active les variables Composer nécessaires pour éviter les erreurs
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1

# Copie le .env au cas où il n'est pas dans le dossier racine
COPY .env .env

# Installe les dépendances PHP sans les dépendances de dev
RUN composer install --no-dev --optimize-autoloader

# Compile les assets via AssetMapper (car tu utilises des fichiers CSS/JS)
RUN php bin/console asset-map:compile

# Ouvre le port 10000 (à adapter si nécessaire)
EXPOSE 10000

# Lance un serveur de développement PHP simple (optionnel : à remplacer par nginx/php-fpm si en prod)
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
