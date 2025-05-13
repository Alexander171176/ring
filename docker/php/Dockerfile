FROM php:8.2-fpm-alpine

WORKDIR /var/www

# Обновляем список пакетов и устанавливаем ca-certificates
RUN apk update && \
    apk add --no-cache ca-certificates && \
    update-ca-certificates

# Комментарии к утилитам оптимизации:
# jpegoptim, optipng, gifsicle - стандартные оптимизаторы
# libwebp-tools - предоставляет утилиту cwebp для конвертации в WebP
# pngquant - для оптимизации PNG с потерями
# nodejs, npm, svgo - для оптимизации SVG

# Устанавливаем основные зависимости и утилиты для оптимизации изображений
RUN apk --update add \
    # Основные зависимости
    wget curl build-base libmemcached-dev libmcrypt-dev libxml2-dev pcre-dev \
    zlib-dev autoconf cyrus-sasl-dev libgsasl-dev oniguruma-dev openssl openssl-dev \
    postgresql-dev freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev \
    imagemagick imagemagick-dev nano unzip \
    # Утилиты оптимизации (без avifenc)
    jpegoptim optipng gifsicle libwebp-tools pngquant

# Устанавливаем Node.js и SVGO
RUN apk add --no-cache nodejs npm && \
    npm install -g svgo

# Устанавливаем расширения PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd pdo_pgsql pcntl exif

# Устанавливаем и активируем Redis и Imagick
RUN pecl install redis imagick && docker-php-ext-enable redis imagick

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename composer

# Копируем конфигурацию PHP
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/php.ini

# Очищаем кеш пакетного менеджера и npm кеш
RUN rm -rf /var/cache/apk/* && \
    rm -rf /tmp/* && \
    npm cache clean --force && \
    rm -rf /root/.npm /root/.node-gyp # Очистка кеша npm для пользователя root

# Очищаем лишние данные для оптимизации образа (если этот путь действительно существует и его можно безопасно удалить)
# RUN rm -rf /usr/share/php8 # Будьте осторожны с этой командой, убедитесь, что она не удаляет нужные части PHP

# Создаем пользователя и группу для www-data, если это необходимо для вашего приложения
# (часто используется для прав доступа к файлам Laravel)
# RUN addgroup -g 1000 www && \
#     adduser -u 1000 -G www -s /bin/sh -D www

# Копируем файлы приложения (это обычно делается в docker-compose.yml через volumes,
# но если вы хотите встроить их в образ, делайте это здесь)
# COPY . .

# Меняем владельца файлов на www-data (если создали пользователя)
# RUN chown -R www:www /var/www

# Открываем порт (хотя это больше для информации, т.к. маппинг портов в docker-compose.yml)
# EXPOSE 9000

# Команда по умолчанию (если не переопределена в docker-compose.yml)
# CMD ["php-fpm"]
