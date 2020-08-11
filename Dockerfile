FROM php:7.4-apache

RUN apt-get update -y \
    && apt-get install -y curl wget libicu-dev git \
    && mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && docker-php-ext-install intl opcache \
    && a2enmod rewrite

WORKDIR /var/www

COPY . .
COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony \
    && symfony check:requirements \
    && composer install
    && rm /usr/local/bin/composer /usr/local/bin/symfony
