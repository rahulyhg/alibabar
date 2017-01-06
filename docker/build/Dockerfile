FROM php:7.0.8-fpm-alpine

RUN \
    apk update \
    && apk add curl git expect bash openssh rsync make tar autoconf g++ file sed \
    && mkdir -p /etc/ssh \
    && ssh-keyscan -H github.com > /etc/ssh/ssh_known_hosts

RUN \
    docker-php-ext-install curl mcrypt pdo pdo_mysql intl json session \
    && pecl install redis xdebug \
    && curl -Ss https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && mkdir .composer \
    && mkdir -p /root/.composer

COPY docker/build/sbin /sbin
RUN chmod +x /sbin/entrypoint.sh
ENTRYPOINT ["/sbin/entrypoint.sh"]