# Imagen base con PHP y Apache
FROM php:8.1-apache

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instala extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copia el proyecto al servidor web
COPY . /var/www/html/

# Ir al directorio
WORKDIR /var/www/html/

# Instala las dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Da permisos al servidor web
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto web
EXPOSE 80
