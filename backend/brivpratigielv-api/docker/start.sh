#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY not set — generating one now"
  php artisan key:generate --force
fi

php artisan config:clear
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
