# Imagen base con PHP y Apache
FROM php:8.3-apache

# Instala las extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    unzip \
    libpq-dev \
    libonig-dev \
    postgresql-17 \
    postgresql-client-17 \
    && docker-php-ext-install pdo_pgsql mbstring \
    && apt-get purge -y --auto-remove libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Copia el proyecto al servidor web
COPY . /var/www/html/

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Ir al directorio
WORKDIR /var/www/html/

# Da permisos al servidor web
RUN chown -R www-data:www-data /var/www/html

# Expone el puerto web
EXPOSE 80

ENV APP_ENV=development \
    ENABLE_TEST_LOGIN=true \
    DB_HOST=127.0.0.1 \
    DB_PORT=5432 \
    DB_NAME=blog \
    DB_USER=blog \
    DB_PASS=blog_local_password

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
