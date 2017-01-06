#!/bin/bash
set -euox

WWW_USER=${WWW_USER:-'www-data'};
WWW_UID=${WWW_UID:-''};
WEBROOT='/var/www/hosts';
NAME=alibabar;
DEPLOY=$WEBROOT/$NAME;
VHOST=$NAME;
DNS=${DNS:-"alibabar"}
DEV=${DEV:-"0"}

if [[ -d "$WEBROOT/$NAME" ]]; then
    echo "Detected source project already exists in webroot.";
    # volume injected, i.e. developer machine
    DEPLOY=$WEBROOT/$NAME;
else
    echo "Untarring project to '$DEPLOY'";
    mkdir -p $DEPLOY;
    tar xf project.tgz -C $DEPLOY --strip-components=1;
    chown -R $WWW_USER:$WWW_USER $DEPLOY;
    chmod 0711 $DEPLOY;
fi

# permission workaround
if [[ $WWW_UID ]]; then
    addgroup -g $WWW_UID -S $WWW_USER
    adduser -S -u $WWW_UID -G $WWW_USER $WWW_USER
fi

# nginx config update
sed -i -e s@'${WWW_USER}'@"$WWW_USER"@g /etc/nginx/nginx.conf
sed -i -e s@'${DNS}'@"$DNS"@g /etc/nginx/sites-enabled/default.conf
sed -i -e s@'${DEPLOY}'@"$DEPLOY"@g /etc/nginx/sites-enabled/default.conf

# php-fpm update
sed -i -e s@'${WWW_USER}'@"$WWW_USER"@g /etc/php/fpm/pool.d/www.conf

# if dev is set to true, enable xdebug, xhprof, etc.
if [[ "$DEV" == 1 ]]; then
    # move dev settings over
    mv /etc/php/fpm/conf.d/dev._ini /usr/local/etc/php/conf.d/dev.ini || true
    rm -f /etc/php/fpm/conf.d/prod._ini || true
else
    # move prod settings over and dev settings back
    mv /etc/php/fpm/conf.d/prod._ini /usr/local/etc/php/conf.d/prod.ini || true
    rm -f /etc/php/fpm/conf.d/dev._ini || true
fi

exec runsvdir -P /etc/service/