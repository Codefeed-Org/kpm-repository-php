FROM php:7.4.3-fpm-alpine
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apk update
RUN apk add libzip-dev postgresql-dev
RUN apk add --no-cache --virtual .build-deps autoconf g++ make
RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip
RUN docker-php-ext-install sockets
RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo pdo_pgsql
RUN pecl install xdebug-2.8.0 && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini;
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN apk del .build-deps
RUN docker-php-source delete
RUN rm -rf /tmp/*
RUN rm -rf /var/cache/apk/*
WORKDIR /webserver/
COPY composer.json .
RUN composer install