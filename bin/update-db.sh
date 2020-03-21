#!/usr/bin/env sh
set -ex

bin/console doctrine:mapping:info || true

bin/console doctrine:migrations:migrate -n || true

bin/console doctrine:fixtures:load -n || true
