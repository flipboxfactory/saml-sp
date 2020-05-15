# DOCS
docs-build:
	yarn docs:build
docs-dev:
	yarn docs:dev

composer-install:
	docker run --rm -it -v "${PWD}:/var/www/html/" flipbox/php:72-apache sh -c "composer install"

phpcs: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    flipbox/php:72-apache sh -c "./vendor/bin/phpcs --standard=psr2 --ignore=./src/web/assets/*/dist/*,./src/migrations/m* ./src"
phpcbf: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    flipbox/php:72-apache sh -c "./vendor/bin/phpcbf --standard=psr2 ./src"

test:
	docker-compose run test
