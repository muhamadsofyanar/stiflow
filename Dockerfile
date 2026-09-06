FROM node:22-alpine AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM composer:2.8 AS dependencies
WORKDIR /build
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        bash icu-libs libzip mysql-client nginx supervisor \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS icu-dev libzip-dev linux-headers \
    && docker-php-ext-install -j"$(nproc)" bcmath intl opcache pcntl pdo_mysql zip \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=dependencies --chown=www-data:www-data /build/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /build/public/build ./public/build
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

RUN rm -f bootstrap/cache/*.php \
    && mkdir -p \
        storage/app/private storage/app/public \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
        storage/logs bootstrap/cache /tmp/nginx/client_body /tmp/nginx/proxy /tmp/nginx/fastcgi \
    && chown -R www-data:www-data storage bootstrap/cache /tmp/nginx \
    && chmod +x docker/entrypoint.sh docker/runtime-smoke.sh

USER www-data
EXPOSE 8080

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["web"]
