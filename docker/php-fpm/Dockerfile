FROM php:8.1-fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it

RUN chmod +x /usr/bin/wait-for-it

# see https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions pdo_mysql imap gd zip intl

COPY php.ini "$PHP_INI_DIR/php.ini"
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# RUN addgroup -g 1000 drupal && adduser -G drupal -g drupal -s /bin/sh -D drupal
# USER drupal

RUN chown -R www-data:www-data /var/www

USER www-data

#CMD composer install ; wait-for-it database:3306 -- bin/console doctrine:migrations:migrate ;  php-fpm
# CMD composer install; php-fpm

EXPOSE 9000
