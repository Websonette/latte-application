FROM composer:2 AS composer

FROM php:8.3-fpm-bookworm

RUN apt-get update \
    && apt-get install --no-install-recommends --yes \
        git \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

RUN git config --system --add safe.directory /workspace

WORKDIR /workspace/examples/dev-app
