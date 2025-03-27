FROM php:8.2-fpm-alpine

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Устанавливаем зависимости
RUN apk --update add wget \
    curl \
    build-base \
    libmemcached-dev \
    libmcrypt-dev \
    libxml2-dev \
    pcre-dev \
    zlib-dev \
    autoconf \
    cyrus-sasl-dev \
    libgsasl-dev \
    oniguruma-dev \
    openssl \
    openssl-dev \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    imagemagick \
    imagemagick-dev \
    nano \
    unzip

# Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd pdo_pgsql pcntl exif

# Устанавливаем и активируем Redis и Imagick
RUN pecl install redis imagick && docker-php-ext-enable redis imagick

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename composer

# Копируем конфигурацию PHP
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Очищаем кеш пакетного менеджера для уменьшения размера образа
RUN rm -rf /var/cache/apk/* && \
    rm -rf /tmp/*

# Очищаем лишние данные для оптимизации образа
RUN rm -rf /usr/share/php8
