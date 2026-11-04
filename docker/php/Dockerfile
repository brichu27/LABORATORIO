# PHP 8.3 + Apache. La imagen ya trae mbstring y fileinfo y respeta los .htaccess (AllowOverride All).
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

COPY php.ini /usr/local/etc/php/conf.d/viviendas.ini
