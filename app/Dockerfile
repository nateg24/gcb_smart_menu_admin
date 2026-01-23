FROM php:8.3-fpm-alpine
RUN apk add --no-cache bash curl git unzip icu-dev libzip-dev \
  && docker-php-ext-install pdo pdo_mysql intl zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-progress || true
COPY . /var/www
RUN composer install --no-interaction --no-progress
