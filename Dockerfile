# BASE IMAGE
FROM php:8.4-fpm-alpine

# Dependências básicas
RUN apk --no-cache add \
    libzip-dev \
    icu-dev \
    libpq-dev \
    nginx \
    libjpeg-turbo-dev \
    libpng-dev \
    freetype-dev \
    imagemagick-dev \
    imagemagick \
    libtool \
    autoconf \
    g++ \
    make \
    pkgconfig \
    ghostscript \
    dcron \
    linux-headers

# Extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    zip \
    intl \
    pgsql \
    pdo_pgsql \
    exif \
    gd \
    sockets \
    pcntl

# Instalar extensões via PECL (imagick e redis)
RUN pecl install imagick redis \
    && docker-php-ext-enable imagick redis

# Aumentar limite de memória do PHP
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

# Limpar cache do apk
RUN rm -rf /tmp/pear \
    && rm -rf /var/cache/apk/*

# Atualizar composer para versão mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuração do nginx e entrypoint
COPY nginx-site.conf /etc/nginx/http.d/default.conf
COPY entrypoint.sh /etc/entrypoint.sh
RUN chmod +x /etc/entrypoint.sh

# Código da aplicação
ADD . /var/www/html
WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

# Instalar dependências PHP
RUN composer install --ignore-platform-req=php --no-dev --optimize-autoloader \
    && php artisan storage:link

# Cron
RUN echo "* * * * * php /var/www/html/artisan schedule:run >> /tmp/schedule.log 2>&1" >> /etc/crontabs/root

# Permissões
RUN chgrp -R www-data /var/www/html/bootstrap /var/www/html/storage \
    && chmod -R g+w /var/www/html/bootstrap /var/www/html/storage

EXPOSE 80

ENTRYPOINT ["/etc/entrypoint.sh"]

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 CMD php || exit 1