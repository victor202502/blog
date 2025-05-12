# Imagen base con PHP y Apache
FROM php:8.1-apache

# Copia todos los archivos a la carpeta del servidor web
COPY . /var/www/html/

# Opcional: habilita módulos si los necesitas
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Da permisos a Apache
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto estándar
EXPOSE 80
