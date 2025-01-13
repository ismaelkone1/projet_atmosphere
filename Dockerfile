FROM php:8.3-apache

# Mise à jour de base
RUN apt-get update && \
    apt-get install --yes --force-yes \
    cron openssl

# Installer le gestionnaire d'extensions PHP
RUN curl -sSLf \
    -o /usr/local/bin/install-php-extensions \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions

# Installer les extensions PHP nécessaires
RUN install-php-extensions xmlrpc xsl intl

# Copier le fichier php.ini personnalisé depuis la racine du projet vers le conteneur
COPY php.ini /usr/local/etc/php/

# Copier le contenu de l'application dans le répertoire web du conteneur
COPY . /var/www/html/

# Donner les permissions appropriées au dossier de l'application
RUN chown -R www-data:www-data /var/www/html
