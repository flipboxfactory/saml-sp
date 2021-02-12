ARG PHP_IMAGE=flipbox/php:74-apache
FROM ${PHP_IMAGE} AS composer

COPY ./composer.json /var/www/html/
COPY ./src ./src

RUN composer install --no-interaction --prefer-dist --no-scripts && \
    ls -l && pwd

FROM ${PHP_IMAGE} AS phpcs

COPY ./src ./src
COPY ./Makefile ./Makefile
COPY --from=composer /var/www/html/vendor ./vendor

RUN make phpcs

FROM ${PHP_IMAGE} AS tests

COPY ./src ./src
COPY ./tests ./tests
COPY ./codeception.yml ./
COPY --from=composer /var/www/html/vendor ./vendor


ENV DB_SERVER db
ENV DB_USER root
ENV DB_PASSWORD password
ENV DB_DATABASE test
ENV DB_DRIVER mysql

