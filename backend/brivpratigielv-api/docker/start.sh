#!/usr/bin/env sh
set -eu

cd /var/www/html

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"