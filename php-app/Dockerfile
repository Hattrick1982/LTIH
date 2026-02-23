FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd exif

WORKDIR /var/www

COPY . /var/www
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

RUN mkdir -p /run/nginx /var/log/supervisor \
    && chown -R www-data:www-data /var/www/storage

EXPOSE 8080

CMD ["supervisord", "-c", "/etc/supervisord.conf"]
