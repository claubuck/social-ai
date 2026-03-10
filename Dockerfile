# Services Fields - Laravel + PHP 8.4 (npm run build lo ejecutás vos manualmente dentro del contenedor)
FROM php:8.4-cli-alpine AS base

RUN apk add --no-cache \
    git \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    icu-dev \
    linux-headers \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        intl \
        bcmath \
        opcache \
        pcntl

# Redis (para Horizon y colas)
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node y npm (para ejecutar npm install / npm run build manualmente dentro del contenedor)
RUN apk add --no-cache nodejs npm

WORKDIR /var/www/html

# Dependencias PHP (sin lock: composer install genera composer.lock desde composer.json)
COPY composer.json ./
RUN git config --global url."https://github.com/".insteadOf "git@github.com:"
RUN composer install

COPY . .

# Build frontend (Vite) para producción (devDependencies necesarias para build)
RUN npm install && npm run build && rm -rf node_modules

RUN composer dump-autoload --optimize

# Permisos storage y bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
# Asegurar LF (evitar "no such file or directory" por CRLF en Windows)
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
