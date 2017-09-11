FROM ubuntu:16.04
RUN apt-get update && apt-get install -qqmy \
    cron \
    ca-certificates \
    supervisor \
    nginx \
    php7.0 \
    php7.0-fpm \
    php7.0-sqlite3 \
    php7.0-mysql \
    php7.0-xml \
    php7.0-soap \
    php7.0-curl \
    libav-tools \
    git

COPY conf/supervisord.conf /etc/supervisor/supervisord.conf

COPY conf/nginx.conf /etc/nginx/
COPY conf/default.vhost /etc/nginx/conf.d/

COPY conf/init.sh /root/
COPY conf/nginx.sh /root/

RUN mkdir -p /run/php

COPY adminer /var/www/adminer
RUN chmod 0777 /var/www/adminer/

COPY phpseclib /var/www/phpseclib

COPY conf/php.ini /etc/php/7.0/fpm/conf.d/40-custom.ini

VOLUME ["/var/www/pub", "/var/www/src"]

CMD ["/usr/bin/supervisord"]

EXPOSE 80
