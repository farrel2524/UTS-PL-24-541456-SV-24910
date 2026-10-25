FROM php:8.2-cli-alpine

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN docker-php-ext-install pdo_mysql

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# I think this is copying app code?? (reminder buat ngapus semua komen :v)
COPY . .

RUN composer dump-autoload --optimize \
    && mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache


USER www-data

EXPOSE 8000

#Devserver stuff idk??
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]