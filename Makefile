PHP_IMAGE := flipbox/php:74-apache
PHPCS_COMMAND := ./vendor/bin/phpcs --standard=psr2 --ignore=./src/web/assets/*/dist/*,./src/migrations/m* ./src
PHPCBF_COMMAND := ./vendor/bin/phpcbf --standard=psr2 --ignore=./src/web/assets/*/dist/*,./src/migrations/m* ./src
#
# DOCS
docs-build:
	yarn docs:build
docs-dev:
	yarn docs:dev

composer-install:
	docker run --rm -it -v "${PWD}:/var/www/html/" $(PHP_IMAGE) sh -c "composer install"

phpcs:
	$(PHPCS_COMMAND)
docker-phpcs: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    $(PHP_IMAGE) sh -c "$(PHPCS_COMMAND)"
phpcbf:
	$(PHPCBF_COMMAND)
docker-phpcbf: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    $(PHP_IMAGE) sh -c "$(PHPCBF_COMMAND)"

test:
	docker-compose run test
