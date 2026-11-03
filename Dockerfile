FROM php:8.2.35-fpm-bookworm

ARG NGINX_VERSION=1.30.6

# Use the upstream security release rather than the older Nixpacks PHP snapshot.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl unzip libicu-dev libonig-dev libpq-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" intl mbstring pgsql pdo_pgsql gd opcache zip \
    && rm -rf /var/lib/apt/lists/*

# Install the tested stable release using only the official Nginx signing keys.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends ca-certificates gnupg; \
    export GNUPGHOME="$(mktemp -d)"; \
    curl --fail --silent --show-error --location \
        https://nginx.org/keys/nginx_signing.key -o "$GNUPGHOME/nginx_signing.key"; \
    gpg --batch --import "$GNUPGHOME/nginx_signing.key"; \
    gpg --batch --export \
        573BFD6B3D8FBC641079A6ABABF5BD827BD9BF62 \
        8540A6F18833A80E9C1653A42FD21310B49F6B46 \
        9E9BE90EACBCDE69FE9B204CBCDCD8A38D88A2B3 \
        > /usr/share/keyrings/nginx-archive-keyring.gpg; \
    test -s /usr/share/keyrings/nginx-archive-keyring.gpg; \
    rm -rf "$GNUPGHOME"; \
    unset GNUPGHOME; \
    printf '%s\n' \
        'deb [signed-by=/usr/share/keyrings/nginx-archive-keyring.gpg] https://nginx.org/packages/debian bookworm nginx' \
        > /etc/apt/sources.list.d/nginx.list; \
    printf 'Package: nginx\nPin: origin nginx.org\nPin-Priority: 900\n' \
        > /etc/apt/preferences.d/99nginx; \
    apt-get update; \
    apt-get install -y --no-install-recommends "nginx=${NGINX_VERSION}-1~bookworm"; \
    test "$(nginx -v 2>&1)" = "nginx version: nginx/${NGINX_VERSION}"; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app
COPY . /app
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader \
    && php -r 'if (PHP_VERSION_ID !== 80235) { exit(1); }' \
    && php-fpm -v

CMD ["bash", "start.sh"]
