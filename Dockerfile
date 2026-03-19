FROM dunglas/frankenphp

WORKDIR /app

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# System deps i rozszerzenia PHP potrzebne typowo dla Symfony + Postgresa
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        opcache \
        intl \
        zip \
        gd \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Najpierw zależności - lepszy cache builda
COPY composer.json composer.lock symfony.lock* ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-scripts

# Potem reszta projektu
COPY . .

# Symfony: cache/logs
RUN mkdir -p var/cache var/log public \
    && chown -R www-data:www-data /app/var /app/public

# Caddy / FrankenPHP config
COPY docker/Caddyfile /etc/frankenphp/Caddyfile

# Build assetów / cache opcjonalnie
RUN composer dump-env prod || true \
    && php bin/console asset-map:compile --env=prod || true \
    && php bin/console assets:install public --env=prod || true \
    && php bin/console cache:clear --env=prod || true \
    && php bin/console cache:warmup --env=prod || true

EXPOSE 80 443 443/udp
