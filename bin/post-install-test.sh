#!/usr/bin/env sh
set -ex

export APP_ENV=test

bin/console doctrine:database:create -n --if-not-exists
bin/console doctrine:schema:drop -n -f --full-database
bin/console doctrine:migrations:migrate -n

bin/console hautelook:fixtures:load -n
