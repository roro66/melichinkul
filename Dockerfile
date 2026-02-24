FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git curl unzip \
    libpq-dev \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql gd opcache pcntl zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instalar Node.js 20.x para compilar assets con Vite
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && node --version \
    && npm --version

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar y configurar entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
