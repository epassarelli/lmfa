# Imagen base
FROM php:8.2-apache

# Instalar dependencias
RUN apt-get update && apt-get install -y \
  curl\
  libzip-dev \
  zip \
  unzip \
  && docker-php-ext-configure zip \
  && docker-php-ext-install zip pdo_mysql

# Configurar Apache
COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Configurar PHP
# COPY .docker/php.ini /usr/local/etc/php/

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Directorio de trabajo
WORKDIR /var/www/html

# Copiar código fuente
COPY . /var/www/html

# Instalar dependencias de Composer
RUN cd /var/www/html && composer install --ignore-platform-reqs --optimize-autoloader --no-dev

# Generar key de Laravel
RUN php artisan key:generate

RUN php artisan storage:link

RUN composer dump-autoload

# Permiso a carpetas de almacenamiento
RUN chown -R www-data:www-data \
  /var/www/html/storage \
  /var/www/html/bootstrap/cache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y ca-certificates curl gnupg

# Descargar y agregar la clave GPG al anillo de claves
RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

# Configurar el repositorio de Node.js
ARG NODE_MAJOR=20
RUN echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list

# Instalar Node.js
RUN apt-get update && apt-get install nodejs -y

# Puerto expuesto
EXPOSE 80

# Comando para ejecutar el servidor Apache
CMD ["apache2-foreground"]