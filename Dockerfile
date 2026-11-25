# ==============================================================================
# Multi-stage Dockerfile para SGI (Sistema de Gestión de Inventario)
# ==============================================================================

# ------------------------------------------------------------------------------
# Etapa 1: Compilación de Frontend con Node.js
# ------------------------------------------------------------------------------
FROM node:20-alpine AS frontend

WORKDIR /app

# Instalar dependencias npm
COPY package.json package-lock.json ./
RUN npm ci

# Copiar archivos fuente necesarios para Vite
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

# Compilar assets para producción
RUN npm run build

# ------------------------------------------------------------------------------
# Etapa 2: Dependencias PHP de Producción con Composer
# ------------------------------------------------------------------------------
FROM composer:2 AS composer-deps

WORKDIR /app

COPY composer.json composer.lock ./

# Instalar dependencias de producción sin scripts ni autoloader
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copiar código para generar el autoloader optimizado
COPY app ./app
COPY bootstrap ./bootstrap
COPY database ./database

RUN composer dump-autoload --optimize --no-dev --no-scripts

# ------------------------------------------------------------------------------
# Etapa 3: Imagen Final de Producción (Apache + PHP 8.3)
# ------------------------------------------------------------------------------
FROM php:8.3-apache AS production

LABEL maintainer="Nodo Group <desarrollo@nodogroup.com>"
LABEL description="SGI - Sistema de Gestión de Inventario"

# Variables de entorno por defecto
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=80
ENV DB_CONNECTION=mysql
ENV DB_PORT=3306

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libicu-dev \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && a2enmod rewrite headers remoteip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configurar Apache DocumentRoot
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default.conf

# Configuración de PHP para producción
COPY docker/php/php.ini /usr/local/etc/php/conf.d/sgi-production.ini

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar aplicación base
COPY . /var/www/html

# Copiar vendor optimizado de la etapa de Composer
COPY --from=composer-deps /app/vendor /var/www/html/vendor

# Copiar assets compilados de la etapa de Node
COPY --from=frontend /app/public/build /var/www/html/public/build

# Configurar permisos de almacenamiento y caché
RUN rm -f /var/www/html/bootstrap/cache/*.php /var/www/html/bootstrap/cache/*.tmp \
    && mkdir -p \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar script de arranque
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
