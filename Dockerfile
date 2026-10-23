FROM php:8.2.35-fpm-bookworm

# Use the upstream security release rather than the older Nixpacks PHP snapshot.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx curl unzip libicu-dev libonig-dev libpq-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl mbstring pgsql pdo_pgsql gd opcache zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY . /app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && php -r 'if (PHP_VERSION_ID !== 80235) { exit(1); }' \
    && php-fpm -v

CMD ["bash", "start.sh"]
