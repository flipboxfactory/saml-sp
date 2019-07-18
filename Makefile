TEST_NAME :=
DEBUG :=
test: test-unit
clean-test: clean-install test-unit

test-unit: phpcbf phpcs
	docker-compose run --rm web sh -c "php ./vendor/bin/codecept run unit ${TEST_NAME} ${DEBUG} --coverage --coverage-html"

test-unit-debug: DEBUG := -vvv -d
test-unit-debug: test-unit

clean:
	rm -rf vendor/ composer.lock cpresources web

composer-install:
	docker run --rm -it -v "${PWD}:/var/www/html/" flipbox/php:72-apache sh -c "composer install"

clean-install: clean composer-install
phpcs: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    flipbox/php:72-apache sh -c "./vendor/bin/phpcs --standard=psr2 --ignore=./src/web/assets/*/dist/*,./src/migrations/m* ./src"
phpcbf: composer-install
	docker run --rm -it -v "${PWD}:/var/www/html" \
	    flipbox/php:72-apache sh -c "./vendor/bin/phpcbf --standard=psr2 ./src"

# DOCS
docs-build:
	npm run docs:build
docs-dev:
	npm run docs:dev
