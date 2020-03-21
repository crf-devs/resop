#!/usr/bin/env sh
set -ex

if [ "prod" == "$ENV_PREFIX" ]; then
    echo "Running in prod env"
    bin/update-db.sh
    exit 0
fi

bin/console doctrine:database:create -n --if-not-exists
bin/console doctrine:schema:drop -n -f --full-database || true

bin/update-db.sh
