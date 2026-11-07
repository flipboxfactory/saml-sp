# Ephemeral Craft 5 + saml-sp sandbox. Built and run by dev/up; see dev/README.md.
FROM php:8.3-apache

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl zip gd bcmath opcache @composer \
    && apt-get update && apt-get install -y --no-install-recommends git unzip default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri 's#/var/www/html#/app/web#g' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /app/web>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/craft.conf \
    && a2enconf craft \
    && printf 'memory_limit=512M\nmax_execution_time=120\n' > /usr/local/etc/php/conf.d/craft.ini

COPY entrypoint.sh /usr/local/bin/sandbox-entrypoint
WORKDIR /app
ENTRYPOINT ["sandbox-entrypoint"]
