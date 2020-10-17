FROM php:7.4-fpm

RUN docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN apt-get update
RUN apt-get install -y git

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug