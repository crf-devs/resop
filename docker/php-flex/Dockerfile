FROM php:7.4-fpm-alpine AS withoutsources

ENV TZ UTC
ENV APP_ENV prod
ENV APP_DEBUG '0'
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /.composer

ARG DEBUG_TOOLS

RUN apk add --update --no-cache \
    openssl \
    ca-certificates \
    curl \
    fcgi \
    su-exec \
    acl \
    file \
    gettext \
    git

RUN set -eux; \
  apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    postgresql-dev \
  ; \
  \
  docker-php-ext-configure zip; \
  docker-php-ext-install -j$(nproc) \
    intl \
    pdo_pgsql \
    zip \
    sockets \
  ; \
  pecl install \
    apcu-5.1.18 \
  ; \
  pecl clear-cache; \
  docker-php-ext-enable \
    apcu \
    opcache \
  ; \
  \
  runDeps="$( \
    scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
      | tr ',' '\n' \
      | sort -u \
      | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
  )"; \
  apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
  \
  apk del .build-deps

RUN curl https://getcomposer.org/composer-1.phar -o /usr/bin/composer \
    && chmod +x /usr/bin/composer \
    && mkdir /.composer \
    && chown -R www-data:www-data /.composer \
    && setfacl -R -m o::rwX /.composer \
    && setfacl -dR -m o::rwX /.composer \
    && su-exec www-data composer global require "hirak/prestissimo" "jderusse/composer-warmup" --prefer-dist --no-progress --no-suggest --classmap-authoritative \
    && su-exec www-data composer clear-cache -n

RUN test -z "$DEBUG_TOOLS" || ( \
        apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
        && pecl install pcov-1.0.6 \
        && pecl clear-cache \
        && docker-php-ext-enable pcov \
        && apk del .build-deps \
    )

RUN test -z "$DEBUG_TOOLS" || ( \
        version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
        && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/alpine/amd64/$version \
        && mkdir -p /tmp/blackfire \
        && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
        && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
        && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > /usr/local/etc/php/conf.d/blackfire.ini \
        && rm -rf /tmp/blackfire-probe.tar.gz \
        && curl -A "Docker" -L https://blackfire.io/api/v1/releases/client/linux_static/amd64 | tar zxp -C /tmp/blackfire \
        && mv /tmp/blackfire/blackfire /usr/bin/blackfire \
        && rm -Rf /tmp/blackfire \
    )

RUN test -z "$DEBUG_TOOLS" || ( \
        apk add --no-cache \
            chromium \
            chromium-chromedriver \
    )

COPY ./docker/php-flex/files/. /

WORKDIR /srv

# ================================================

FROM withoutsources AS withoutsources-fpm

EXPOSE 9000
ENTRYPOINT ["entrypoint"]
CMD []
HEALTHCHECK --interval=5s --timeout=5s --start-period=5s --retries=3 CMD REDIRECT_STATUS=true SCRIPT_FILENAME=/srv/public/ping.php REQUEST_URI=/ REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000
STOPSIGNAL SIGQUIT

# ================================================

FROM node:13-alpine AS withsources-npm

WORKDIR /srv

COPY package.json webpack.config.js yarn.lock ./

RUN apk --no-cache --update --virtual build-dependencies add \
    python \
    make \
    g++ \
    && yarn install --pure-lockfile \
    && apk del build-dependencies

COPY assets assets/
RUN yarn encore production

# ================================================

FROM withoutsources AS withsources

ARG BUILD_TAG=dev
ENV IMAGE_BUILD_TAG=$BUILD_TAG

COPY --chown=www-data:www-data composer.* symfony.lock /srv/

RUN chown -R www-data:www-data /srv
USER www-data

RUN composer install --no-dev --no-scripts --prefer-dist --no-suggest && composer clear-cache -n

COPY --chown=www-data:www-data .env ./
RUN composer dump-env prod && rm .env

COPY --chown=www-data:www-data assets assets/
COPY --chown=www-data:www-data bin bin/
COPY --chown=www-data:www-data config config/
COPY --chown=www-data:www-data public public/
COPY --chown=www-data:www-data templates templates/
COPY --chown=www-data:www-data translations translations/
COPY --chown=www-data:www-data src src/
COPY --from=withsources-npm --chown=www-data:www-data /srv/public/build public/build/

RUN set -eux; \
  mkdir -p var/cache var/log; \
  composer dump-autoload --optimize --apcu --classmap-authoritative --no-dev; \
  composer run-script --no-dev post-install-cmd; \
  chmod +x bin/console; sync

RUN set -eux; \
  bin/console cache:warmup;

# TODO Add the opcache dump when opcache.preload will be fixed (see php.ini);

USER root

# ================================================
# Stop the build here if you want an image with sources and fpm

FROM withsources AS withsources-fpm

ENTRYPOINT ["entrypoint"]
CMD []
HEALTHCHECK --interval=5s --timeout=5s --start-period=5s --retries=3 CMD REDIRECT_STATUS=true SCRIPT_FILENAME=/srv/public/ping.php REQUEST_URI=/ REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000
STOPSIGNAL SIGQUIT

# ================================================
# Stop the build here if you want an image with sources, fpm AND nginx

FROM withsources-fpm AS withsources-nginx

RUN apk add --no-cache nginx

COPY ./docker/nginx/files/etc/nginx /etc/nginx

RUN echo "upstream php-upstream { server 127.0.0.1:9000; }" > /etc/nginx/conf.d/upstream.conf
RUN sed -i 's/listen = 0.0.0.0:9000/listen = 127.0.0.1:9000/g' /usr/local/etc/php-fpm.conf

EXPOSE 80

HEALTHCHECK --interval=5s --timeout=5s --start-period=5s --retries=3 CMD curl -s http://0.0.0.0/ping.php 1>/dev/null || exit 1
