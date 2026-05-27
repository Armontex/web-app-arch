FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libonig-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
