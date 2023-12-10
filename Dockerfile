# Utilisez une image Symfony
FROM php:8.2-apache

# Le reste du Dockerfile reste inchangé...


# Installez les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev

# Activez les extensions PHP nécessaires
RUN docker-php-ext-install \
    pdo_mysql \
    intl \
    zip

# Activez le module Apache mod_rewrite
RUN a2enmod rewrite

# Copiez les fichiers du projet Symfony dans le conteneur
COPY . /var/www/html

# Définissez le répertoire de travail
WORKDIR /var/www/html

# Installez les dépendances Symfony
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction

# Exposez le port 80
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]
