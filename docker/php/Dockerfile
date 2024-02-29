FROM php:8.2-apache AS base

MAINTAINER Papp Dániel <daniel.papp95@gmail.com>

COPY --from=composer /usr/bin/composer /usr/bin/composer

# configure and install extra dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        # zip extension dependency
        libzip-dev \
        unzip \
    && apt-get clean \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
    # disable default apache vhost
    && a2dissite 000-default.conf \
    # enable apache rewrite engine
    && a2enmod rewrite \
    # add directory for composer cache
    && mkdir /var/www/.composer \
    && chmod 0777 /var/www/.composer
