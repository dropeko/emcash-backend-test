FROM php:8.3-alpine

RUN docker-php-ext-install pdo_mysql

WORKDIR /var/www/html

RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/bin --filename=composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]