FROM php:7.1-fpm-alpine

RUN apk add --no-cache --virtual .persistent-deps \
		git \
		icu-libs \
		zlib \
		libxml2-dev

ENV APCU_VERSION 5.1.8


RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_pgsql soap pgsql

RUN set -xe \
	&& apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		zlib-dev \
	&& docker-php-ext-install \
		intl \
		zip \
	&& pecl install \
		apcu-${APCU_VERSION} \
	&& docker-php-ext-enable --ini-name 20-apcu.ini apcu \
	&& docker-php-ext-enable --ini-name 05-opcache.ini opcache \
	&& apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/php.ini

COPY docker/php/install-composer.sh /usr/local/bin/docker-app-install-composer

RUN chmod +x /usr/local/bin/docker-app-install-composer

RUN set -xe \
	&& apk add --no-cache --virtual .fetch-deps \
		openssl \
	&& docker-app-install-composer \
	&& mv composer.phar /usr/local/bin/composer \
	&& apk del .fetch-deps

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /srv/app

#COPY composer.json ./
#COPY composer.lock ./

COPY ./app /srv/app

COPY docker/php/start.sh /usr/local/bin/docker-app-start

RUN chmod +x /usr/local/bin/docker-app-start

CMD ["docker-app-start"]
