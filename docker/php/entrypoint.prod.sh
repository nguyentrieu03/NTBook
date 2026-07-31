#!/bin/sh
set -e
# Chạy sau khi container start - lúc này env vars đã được inject
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
exec "$@"