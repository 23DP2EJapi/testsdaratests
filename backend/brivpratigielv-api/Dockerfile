FROM node:22-alpine AS vite

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

FROM php:8.2-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite \
    sqlite-dev \
  && docker-php-ext-install \
    bcmath \
    intl \
    pdo_mysql \
    pdo_sqlite \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --no-progress --prefer-dist --no-scripts

COPY . .

COPY --from=vite /app/public/build ./public/build

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache database \
  && touch database/database.sqlite \
  && chown -R www-data:www-data storage bootstrap/cache database public/build

COPY docker/start.sh /usr/local/bin/start-laravel.sh
RUN chmod +x /usr/local/bin/start-laravel.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/start-laravel.sh"]
