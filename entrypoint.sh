#!/bin/sh
set -e

php bin/console doctrine:migrations:migrate --no-interaction || true
php -S 0.0.0.0:10000 -t public
