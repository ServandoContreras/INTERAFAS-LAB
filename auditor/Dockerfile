FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libcurl4-openssl-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql curl zip \
    && rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite headers
