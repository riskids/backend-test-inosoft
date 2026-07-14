FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

WORKDIR /var/www
COPY . .
RUN composer install
CMD ["php-fpm"]
