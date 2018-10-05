composer-update:
	docker-compose exec web sh -c "cd plugin && composer update"
composer-install-plugin:
	docker-compose exec web sh -c "cd plugin && composer install"
test: test-unit

test-unit:
	docker-compose exec web sh -c "cd plugin && php ./vendor/bin/codecept run unit --coverage --coverage-xml"

