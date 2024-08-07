# NOTE: this file is executed from the ROOT DIRECTORY of the project
FROM php:8.3-fpm-bookworm

RUN apt-get update -y && \
    apt-get install --no-install-recommends -y \
        libzip-dev zip unzip \
        libicu-dev \
        libxslt1-dev \
        nginx-light \
        libvips libvips-dev \
        supervisor \
        procps \
        redis \
        git \
        acl \
        libpq-dev \
        ffmpeg && \
    mkdir -p /var/log/supervisor && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install intl bcmath pdo pdo_mysql mysqli xsl zip && \
    pecl install vips && \
    echo "extension=vips.so" > /usr/local/etc/php/conf.d/vips.ini && \
    pecl install redis && docker-php-ext-enable redis && \
    docker-php-ext-install opcache && docker-php-ext-enable opcache && \
    pecl install apcu && docker-php-ext-enable apcu

# Latest releases available at https://github.com/aptible/supercronic/releases
ENV SUPERCRONIC_URL=https://github.com/aptible/supercronic/releases/download/v0.2.29/supercronic-linux-amd64 \
    SUPERCRONIC=supercronic-linux-amd64 \
    SUPERCRONIC_SHA1SUM=cd48d45c4b10f3f0bfdd3a57d054cd05ac96812b

RUN curl -fsSLO "$SUPERCRONIC_URL" \
 && echo "${SUPERCRONIC_SHA1SUM}  ${SUPERCRONIC}" | sha1sum -c - \
 && chmod +x "$SUPERCRONIC" \
 && mv "$SUPERCRONIC" "/usr/local/bin/${SUPERCRONIC}" \
 && ln -s "/usr/local/bin/${SUPERCRONIC}" /usr/local/bin/supercronic

COPY --from=composer:2.7.2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
    composer global config --no-plugins allow-plugins.symfony/flex true; \
	composer global require "symfony/flex" --prefer-dist --no-progress --classmap-authoritative; \
	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=dunglas/mercure:legacy-v0.11 /mercure /usr/bin/mercure

# add colored shell env to distinguish environments properly
# default value
ENV SHELL_ENV_DISPLAY=dev
ADD deployment/config-files/bash.colorprompt.sh /etc/bash.colorprompt.sh
RUN echo '. /etc/bash.colorprompt.sh' >>  /etc/bash.bashrc

# application entrypoint
ADD /deployment/docker/entrypoint.sh /deployment/docker/entrypoint-e2e.sh /
ADD /deployment/docker/entrypoint.sh /deployment/docker/entrypoint-integration.sh /
ADD /deployment/config-files/php.override.ini /usr/local/etc/php/conf.d/php.override.ini

RUN rm -Rf /usr/local/etc/php-fpm.*
ADD deployment/config-files/php-fpm.conf /usr/local/etc/php-fpm.conf

# see docker-compose file build args
ARG NGINX_SELF_SIGNED_CERT_FQDN
ARG CREATE_NGINX_SELF_SIGNED_CERT

ADD /deployment/config-files/nginx.conf /deployment/config-files/nginx-saml-test.conf /deployment/config-files/nginx-e2e-test-server.conf /etc/nginx/
RUN [ -z $CREATE_NGINX_SELF_SIGNED_CERT ] || [ ! $CREATE_NGINX_SELF_SIGNED_CERT -eq 1 ] && rm /etc/nginx/nginx-saml-test.conf || true;

RUN mkdir -p /var/lib/nginx /usr/local/var/log/ && \
    chown -R www-data /var/lib/nginx /usr/local/var/log/ /etc/nginx/

WORKDIR /app

# prevent the reinstallation of vendors at every changes in the source code
COPY app/composer.json app/composer.lock app/symfony.lock ./

# NOTE: we install dev packages as well for production, because we want to run Behat E2E tests from within the PROD container
# via /entrypoint-e2e.sh
RUN set -eux; \
	composer install --prefer-dist --no-scripts --no-progress; \
	composer clear-cache

# these will be dumped and deleted in entrypoint.sh depending on the APP_ENV
# This is only used by our CI-image -> in development these are simply mounted
COPY app/.env ./
COPY app/.env.test ./

# copy only specifically what we need
COPY app/VERSION VERSION
COPY app/bin bin/
COPY app/config config/
COPY app/public public/
COPY app/migrations migrations/
COPY app/src src/
COPY app/templates templates/
COPY app/translations translations/
COPY app/tests tests/
COPY app/behat.yml.dist behat.yml.dist
COPY app/phpunit.xml.dist phpunit.xml.dist
COPY app/features features/
COPY app/composer.json app/composer.lock /app/

RUN set -eux; \
	mkdir -p var/cache var/log; \
	# composer dump-autoload --classmap-authoritative --no-dev; \
	# composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync
VOLUME /app/var

# generate a dummy self-signed SSL certificate; so SAML can be tested locally.
RUN [ ! -z $CREATE_NGINX_SELF_SIGNED_CERT ] && [ $CREATE_NGINX_SELF_SIGNED_CERT -eq 1 ] && openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -subj "/C=DE/ST=Sachsen/L=Dresden/O=Global Security/OU=IT Department/CN=${NGINX_SELF_SIGNED_CERT_FQDN}" \
    -keyout /etc/ssl/private/nginx-selfsigned.key -out /etc/ssl/certs/nginx-selfsigned.crt \
    || true;

COPY /deployment/config-files/supervisord.conf /etc/supervisor/supervisord.conf
COPY /deployment/docker/degree_crontab /etc/cron.d/degree_crontab

# Give execution rights on the cron tab
RUN chmod 0644 /etc/cron.d/degree_crontab
# Create the log file to be able to run tail
RUN touch /app/var/log/cron.log

COPY deployment/docker/entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
