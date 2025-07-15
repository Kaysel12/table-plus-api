# Usar PHP 8.2 con Apache
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libxslt-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    soap \
    xsl

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copiar archivos composer primero
COPY composer.json composer.lock ./

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Copiar el resto de archivos del proyecto
COPY . .

# Renombrar archivos para que cumplan PSR-4
RUN if [ -f "./app/utils/CacheHelper.php" ]; then \
        mkdir -p ./app/Utils && \
        mv ./app/utils/CacheHelper.php ./app/Utils/CacheHelper.php; \
    fi

RUN if [ -f "./app/utils/ApiResponse.php" ]; then \
        mkdir -p ./app/Utils && \
        mv ./app/utils/ApiResponse.php ./app/Utils/ApiResponse.php; \
    fi

# Eliminar directorio utils vacío si existe
RUN if [ -d "./app/utils" ] && [ -z "$(ls -A ./app/utils)" ]; then \
        rmdir ./app/utils; \
    fi

# Generar autoloader
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer puerto
EXPOSE 80

CMD ["docker-entrypoint.sh"]