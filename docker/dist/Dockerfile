FROM php:7.0.8-fpm-alpine

RUN \
    echo "@comm http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
    && apk upgrade --update \
    && apk add bash tar sed nginx runit@comm \
    && docker-php-ext-enable redis pdo pdo_mysql json session 2>&1 > /dev/null || true

WORKDIR /opt

COPY tmp /usr/local/lib/php/extensions/no-debug-non-zts-20151012
COPY docker/dist/etc /etc
COPY project.tgz project.tgz
COPY docker/dist/sbin /sbin
RUN chmod +x /sbin/entrypoint.sh && mkdir -p /etc/nginx/sites-enabled

EXPOSE 80/tcp 443/tcp
ENTRYPOINT ["/sbin/entrypoint.sh"]