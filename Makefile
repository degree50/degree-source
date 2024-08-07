help:
	cat Makefile

build:
	docker compose build --pull

start:
	docker compose up -d

stop:
	docker compose stop

down:
	docker compose down --remove-orphans --volumes

enter:
	docker-compose exec degree /bin/bash

logs:
	docker compose logs -f

logs-degree:
	docker compose logs -f degree

import-fixtures:
	docker compose exec degree php bin/console doctrine:fixtures:load

migration-generate:
	docker-compose exec degree php bin/console doctrine:migrations:diff

migration-migrate:
	docker-compose exec degree php bin/console doctrine:migrations:migrate

test-integration:
	docker compose exec \
		--env PLAYWRIGHT_API_URL="http://host.docker.internal:3000" \
		--env SYSTEM_UNDER_TEST_URL_FOR_PLAYWRIGHT="http://localhost:9090" \
		--env APP_ENV=test \
		degree ./vendor/bin/behat --tags integration

test-e2e:
	docker compose exec \
		--env PLAYWRIGHT_API_URL="http://host.docker.internal:3000" \
		--env SYSTEM_UNDER_TEST_URL_FOR_PLAYWRIGHT="http://localhost:9090" \
		--env APP_ENV=test \
		degree ./vendor/bin/behat --tags playwright

test-debug:
	docker compose exec \
		--env PLAYWRIGHT_API_URL="http://host.docker.internal:3000" \
		--env SYSTEM_UNDER_TEST_URL_FOR_PLAYWRIGHT="http://localhost:9090" \
		--env APP_ENV=test \
		degree ./vendor/bin/behat --tags debug

test:
	docker compose exec \
		--env PLAYWRIGHT_API_URL="http://host.docker.internal:3000" \
		--env SYSTEM_UNDER_TEST_URL_FOR_PLAYWRIGHT="http://localhost:9090" \
		--env APP_ENV=test \
		degree ./vendor/bin/behat
