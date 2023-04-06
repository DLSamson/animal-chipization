FROM php:8.2-fpm

RUN apt-get update && apt-get install curl zip -y libpq-dev
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql pgsql opcache

COPY --from=composer/composer:latest-bin /composer /user/bin/composer