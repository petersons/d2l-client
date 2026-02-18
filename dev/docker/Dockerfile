ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-cli-alpine
LABEL maintainer="Antonio Pauletich <antonio.pauletich95@gmail.com>"

ARG COMPOSER_VERSION=2.9.5
ARG XDEBUG_VERSION=3.5.0

ENV XDEBUG_START_WITH_REQUEST "no"

# This is where we're going to store all of our non-project specific binaries
RUN mkdir -p /app/bin
ENV PATH /app/bin:$PATH

# Install needed core and PECL extensions
RUN apk add --update --no-cache --virtual .build-deps \
        ${PHPIZE_DEPS} \
        libzip-dev \
        zlib-dev \
        linux-headers \
    && docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        zip \
    && pecl install \
        xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable \
        xdebug \
    && apk del --purge .build-deps

RUN mv ${PHP_INI_DIR}/php.ini-development ${PHP_INI_DIR}/php.ini

RUN echo '[xdebug]' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.start_with_request = ${XDEBUG_START_WITH_REQUEST}' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.mode = debug,coverage' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.discover_client_host = false' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_host = %XDEBUG_CLIENT_HOST%' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini

# Utilities needed to run this image
RUN apk add --update --no-cache \
        git \
        libzip \
        unzip \
        su-exec \
        shadow

# Composer
RUN curl --show-error https://getcomposer.org/installer | php -- \
        --install-dir=/app/bin \
        --filename=composer \
        --version=${COMPOSER_VERSION}

# Create the user that's going to run our application
RUN useradd -ms /bin/sh app

COPY entrypoint.sh /usr/local/bin/docker-entrypoint

VOLUME /app/src
WORKDIR /app/src

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-a"]
