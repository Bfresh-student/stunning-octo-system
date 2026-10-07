FROM php:8.3-apache

# 1. Mise à jour et installation des dépendances système de base
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. Installation des extensions PHP nécessaires (PDO MySQL et Zip)
RUN docker-php-ext-install pdo pdo_mysql

# 3. Activation du module de réécriture d'URL Apache (mod_rewrite)
RUN a2enmod rewrite

# 4. Modification du DocumentRoot pour pointer vers le dossier public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Autoriser les règles de réécriture .htaccess dans Apache
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 6. Définir le répertoire de travail
WORKDIR /var/www/html

# 7. Copier tous les fichiers du projet dans le conteneur
COPY . /var/www/html/

# 8. Créer le dossier d'uploads et donner les droits à l'utilisateur Apache (www-data)
RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/app

EXPOSE 80

CMD ["apache2-foreground"]