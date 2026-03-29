FROM php:8.2-apache

# Abilita mod_rewrite e installa l'estensione curl
RUN a2enmod rewrite \
    && docker-php-ext-install -j"$(nproc)" \
    && apt-get update \
    && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

# Copia i sorgenti nella document root di Apache
COPY src/ /var/www/html/

# Permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
