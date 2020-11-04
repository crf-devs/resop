
DOCKER_COMPOSE_UP=docker-compose up --no-recreate --remove-orphans -d
DOCKER_COMPOSE_UP_RECREATE=docker-compose up --force-recreate --remove-orphans -d

all: pre-configure configure build vendors webpack-build-dev start

restart: clean all

pre-configure:
	@echo "Checking docker command"         && command -v "docker" > /dev/null 2>&1            || (echo "You have to install the "docker" command" && false)
	@echo "Checking docker-compose command" && command -v "docker-compose" > /dev/null 2>&1    || (echo "You have to install the "docker-compose" command" && false)

configure:
	test -f docker-compose.override.yml || cp docker-compose.override.yml.dist docker-compose.override.yml
ifeq ($(shell uname -s), Darwin)
	sed -i '' 's/#\(.*\)# Uncomment for MacOS/\1/g' docker-compose.override.yml
endif
	mkdir -p .cache/ssl .cache/node
	test -f .cache/ssl/local.crt || (docker run --rm -v $$(pwd):/src alpine:3.9 sh -c "apk add openssl && openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /src/.cache/ssl/local.key -out /src/.cache/ssl/local.crt -subj \"/C=FR/ST=Paris/L=Paris/O=Resop/CN=*.resop.docker\" && cat /src/.cache/ssl/local.crt /src/.cache/ssl/local.key > /src/.cache/ssl/local.pem  && chown -R $$(id -u):$$(id -g) /src/.cache")

unconfigure:
	rm -f .env.local docker-compose.override.yml

#
# DOCKER
#

pull:
	docker-compose pull

build:
	docker-compose build --pull

build-prod:
	docker build -t resop:latest -f docker/php-flex/Dockerfile .

start-db:
	$(DOCKER_COMPOSE_UP) traefik postgres adminer
	docker-compose run --rm wait -c postgres:5432

start-php:
	$(DOCKER_COMPOSE_UP_RECREATE) traefik nginx fpm
	docker-compose run --rm wait -c fpm:9000,nginx:80
	@echo -n "\nStack started with success:\nhttp://resop.vcap.me:7500/login => user1@resop.com : 01/01/1990"
	@echo -n "\nhttp://resop.vcap.me:7500/organizations/login => DT75 : covid19\n"

start: init-db start-php

start-preserve-db: start-db start-php

stop:
	docker-compose stop

ps:
	docker-compose ps

logs:
	docker-compose logs -f --tail 10

clear-cache:
	rm -rf var/*

clean: clear-cache
	docker-compose down -v --remove-orphans || true
	rm -f helpme.log
	$(MAKE) -s unconfigure

#
# PROJECT
#

vendors:
	bin/tools composer install -n -v --profile --apcu-autoloader --prefer-dist --ignore-platform-reqs
	bin/node-tools yarn install --pure-lockfile

build-js-routes:
	bin/tools bin/console fos:js-routing:dump --format=json --target=assets/js/fos_js_routes.json

webpack-build-dev:
	bin/node-tools yarn encore dev

webpack-watch-dev:
	bin/node-tools yarn encore dev --watch

webpack-build-prod:
	bin/node-tools yarn encore production

init-db: start-db
	bin/tools rm -rf var/cache/*
	bin/tools bin/post-install-dev.sh

fix-cs-php:
	bin/tools vendor/bin/php-cs-fixer fix --allow-risky yes --verbose
	bin/tools vendor/bin/phpcbf

fix-cs: fix-cs-php
	bin/node-tools npm run lint:fix
	bin/node-tools npm run lint:css:fix

#
# TESTS
#

test: test-cs test-advanced test-unit test-behat

test-cs:
	bin/tools vendor/bin/php-cs-fixer fix --allow-risky yes --dry-run --verbose --diff
	bin/tools vendor/bin/phpcs
	bin/tools bin/console lint:twig templates --env=test
	bin/tools bin/console lint:yaml config --parse-tags --env=test
	bin/node-tools npm run lint
	bin/node-tools npm run lint:css

test-advanced:
	bin/tools sh -c "APP_DEBUG=1 APP_ENV=test bin/console c:w"
	bin/tools vendor/bin/phpstan analyse --configuration phpstan.neon --level max --no-progress .

test-behat:
	bin/tools sh -c "APP_ENV=test bin/console c:c"
	bin/tools sh -c "APP_DEBUG=1 APP_ENV=test bin/post-install-test.sh"
	bin/tools vendor/bin/behat --format=progress

test-unit:
	bin/tools sh -c "APP_DEBUG=1 APP_ENV=test bin/post-install-dev.sh"
	bin/tools vendor/bin/phpunit

test-coverage:
	bin/tools sh -c "APP_DEBUG=1 APP_ENV=test bin/post-install-dev.sh"
	bin/tools sh -c "APP_DEBUG=0 vendor/bin/phpunit --coverage-text"
	bin/tools sh -c "COVERAGE=true vendor/bin/behat --format=progress"

move-test-profiler:
	bin/tools sh -c "rm -rf var/cache/dev/profiler && mkdir -p var/cache/dev && cp -R var/cache/test/profiler var/cache/dev/profiler"
	@echo "Done : http://resop.vcap.me:7500/_profiler/search?limit=10"

#
# Help commands
#

helpme:
	@echo "Generating the helpme.log file..."
	@$(MAKE) -s helpme-logs > helpme.log
	@echo "Done, you can send the 'helpme.log' file to your team :)"

helpme-logs:
	$(MAKE) -s pre-configure || true
	@echo "=========================="
	id || true
	@echo "=========================="
	git fetch -ap 2>&1 || true
	git status 2>&1 || true
	@echo "=========================="
	docker info 2>&1 || true
	@echo "=========================="
	docker-compose version 2>&1 || true
	@echo "=========================="
	(command -v "docker-machine" > /dev/null 2>&1 && docker-machine ls) || true
	@echo "=========================="
	(command -v "dinghy" > /dev/null 2>&1 && dinghy status) || true
	@echo "=========================="
	docker-compose config
	@echo "=========================="
	docker ps -a
	@echo "=========================="
	docker-compose ps
	@echo "=========================="
	docker-compose logs --no-color -t --tail=10
