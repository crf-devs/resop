#!/usr/bin/env sh
set -ex

bin/console doctrine:database:create -n --if-not-exists
bin/console doctrine:schema:drop -n -f --full-database || true

bin/update-db.sh

bin/console doctrine:fixtures:load -n || true
