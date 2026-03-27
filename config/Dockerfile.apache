FROM php:8.4-apache

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip opcache intl \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite proxy proxy_http

WORKDIR /var/www/html

RUN groupadd -g ${GROUP_ID} symfony || true \
    && useradd -u ${USER_ID} -g symfony -m symfony || true \
    && chown -R symfony:symfony /var/www/html

ENV APACHE_RUN_USER=symfony
ENV APACHE_RUN_GROUP=symfony

USER symfony

EXPOSE 80
