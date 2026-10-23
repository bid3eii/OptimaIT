FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos
COPY . /var/www/html/

# Asegurar permisos en la carpeta data para PHP
RUN mkdir -p /var/www/html/data && \
    chown -R www-data:www-data /var/www/html/data && \
    chmod -R 775 /var/www/html/data

EXPOSE 80

CMD ["apache2-foreground"]
