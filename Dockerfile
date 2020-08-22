FROM php:7.4-cli-buster

WORKDIR /service

RUN apt-get update -y \
    && apt-get install -y libc-client2007e-dev libkrb5-dev curl wget libicu-dev git cron \
    && mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install sockets pdo_mysql imap iconv fileinfo intl opcache

COPY . .
COPY ./docker/docker-service-entrypoint /usr/local/bin/
COPY ./docker/app-command /usr/local/bin/
COPY ./docker/crontab /etc/cron.d/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony/bin/symfony /usr/local/bin/symfony \
    && chmod +x /usr/local/bin/docker-service-entrypoint \
    && chmod +x /usr/local/bin/app-command \
    && composer install \
    && composer dump-env prod \
    && symfony check:requirements \
    && rm /usr/local/bin/composer /usr/local/bin/symfony \
    && touch /var/log/cron.log \
    && crontab /etc/cron.d/crontab \
    && rm /etc/cron.d/crontab \
    && apt-get purge -y git

ENV USE_ZEND_ALLOC 0
ENV SERVICE_PATH "/service"

ENTRYPOINT ["docker-service-entrypoint"]

CMD cron -f
