composer-install-plugin:
	docker-compose exec web sh -c "cd plugin && composer install"
test:
	docker-compose exec web sh -c "cd plugin && ./vendor/bin/codecept run --coverage --coverage-xml -vv"
