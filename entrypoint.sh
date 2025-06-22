#!/bin/sh

set -e

# Executar cron
crond -f -l 2 &

# Limpar caches antigos
php artisan config:clear
php artisan cache:clear

# Gerar caches novos
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar autoload
composer dump-autoload
php artisan optimize

# Start PHP e NGINX
php-fpm &
exec nginx -g 'daemon off;'