# Indique la syntaxe Dockerfile pour activer des fonctionnalités avancées de BuildKit
# Cela ne change pas le comportement normal du Dockerfile mais est recommandé
# Plus d'infos : https://docs.docker.com/build/building/syntax/
# --------------------------------------------------------
# syntax=docker/dockerfile:1
    
# Utilise l’image officielle PHP avec PHP-FPM (FastCGI Process Manager) en version 8.2
# PHP-FPM est nécessaire pour exécuter PHP derrière un serveur comme NGINX
FROM php:8.2-fpm
    
# Mise à jour des paquets et installation des dépendances système et des extensions PHP nécessaires
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
    # Installe les extensions PHP : pdo_mysql (MySQL), intl (i18n), opcache (perf), zip (compression)

# Installation de Node.js et npm (nécessaire pour gérer les assets front dans Symfony)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@latest
    # npm@latest pour s'assurer d'avoir la dernière version compatible

# Copie de Composer depuis l’image officielle "composer"
# Cette méthode évite d’installer Composer manuellement
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail principal dans le conteneur
# C’est ici que tout le code Symfony sera copié et exécuté
WORKDIR /var/www/html

# Copie l’ensemble du projet Symfony dans le conteneur
COPY . .

# Installation des dépendances PHP via Composer
# --no-interaction : évite les questions interactives (CI friendly)
# --optimize-autoloader : optimise l'autoload pour de meilleures performances (prod)
RUN composer install --no-interaction --optimize-autoloader

# Donne les bons droits à l’utilisateur www-data sur les dossiers critiques
# Ces dossiers doivent être modifiables pour éviter des erreurs de permission
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Expose le port 9000 utilisé par PHP-FPM
# Cela permet à NGINX (ou tout autre service) de se connecter à PHP-FPM via ce port
EXPOSE 9000

# CMD : Définit la commande à exécuter par défaut quand le conteneur démarre
CMD ["php-fpm"]
