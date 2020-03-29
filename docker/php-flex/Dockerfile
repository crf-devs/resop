FROM php:7.4-fpm-alpine AS withoutsources

ENV TZ UTC
ENV APP_ENV prod
ENV APP_DEBUG '0'
ENV COMPOSER_MEMORY_LIMIT -1
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /.composer

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

RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer \
    && chmod +x /usr/bin/composer \
    && mkdir /.composer \
    \
    && chown -R www-data:www-data /.composer \
    && setfacl -R -m o::rwX /.composer \
    && setfacl -dR -m o::rwX /.composer \
    \
    && su-exec www-data composer global require "hirak/prestissimo" "jderusse/composer-warmup" --prefer-dist --no-progress --no-suggest --classmap-authoritative \
    && su-exec www-data composer clear-cache -n

COPY ./docker/php-flex/files/. /

WORKDIR /srv

# ================================================

FROM withoutsources AS withoutsources-fpm

EXPOSE 9001
ENTRYPOINT ["/bin/entrypoint"]
CMD ["php-fpm"]
HEALTHCHECK --interval=5s --timeout=5s --start-period=5s --retries=3 CMD REDIRECT_STATUS=true SCRIPT_FILENAME=/srv/public/ping.php REQUEST_URI=/ REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9001
STOPSIGNAL SIGQUIT

# ================================================

FROM node:13-alpine AS withsources-npm

WORKDIR /srv

COPY package.json webpack.config.js yarn.lock ./
RUN yarn install --pure-lockfile

COPY assets assets/
RUN yarn encore production

# ================================================

FROM withoutsources AS withsources

COPY --chown=www-data:www-data composer.* symfony.lock /srv/

RUN chown -R www-data:www-data /srv
USER www-data

RUN composer install --no-dev --ignore-platform-reqs --no-scripts --prefer-dist --no-suggest && composer clear-cache -n

COPY --chown=www-data:www-data .env ./
RUN composer dump-env prod && rm .env

COPY --chown=www-data:www-data assets assets/
COPY --chown=www-data:www-data bin bin/
COPY --chown=www-data:www-data config config/
COPY --chown=www-data:www-data public public/
COPY --chown=www-data:www-data templates templates/
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

FROM withsources AS withsources-fpm

EXPOSE 9001
ENTRYPOINT ["/bin/entrypoint"]
CMD ["php-fpm"]
HEALTHCHECK --interval=5s --timeout=5s --start-period=5s --retries=3 CMD REDIRECT_STATUS=true SCRIPT_FILENAME=/srv/public/ping.php REQUEST_URI=/ REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9001
STOPSIGNAL SIGQUIT

ARG BUILD_TAG=dev
ENV IMAGE_BUILD_TAG=$BUILD_TAG

# ================================================
