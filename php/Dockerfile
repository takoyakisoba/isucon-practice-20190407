FROM php:5.6-fpm-alpine

COPY ./docker_files/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY ./docker_files/php/php/php.ini /usr/local/etc/php/conf.d/php.ini

RUN apk add --no-cache nginx make
COPY ./docker_files/nginx/ /etc/nginx
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

# NOTE: see https://github.com/docker-library/php/issues/240#issuecomment-305038173
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

WORKDIR /workspace

ENTRYPOINT ["./docker_files/entry_point.sh"]
