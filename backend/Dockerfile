FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    bash \
    libpng-dev \
    libpq-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    autoconf \
    gcc \
    g++ \
    make \
    linux-headers \
    postgresql-client

RUN docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        xml \
        zip \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
