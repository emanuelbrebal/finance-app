#!/bin/sh
set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor missing, running composer install..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ] && [ -f .env.example ]; then
    echo "[entrypoint] .env missing, copying .env.example..."
    cp .env.example .env
fi

if [ -f .env ] && ! grep -qE '^APP_KEY=base64:' .env; then
    echo "[entrypoint] APP_KEY missing, generating..."
    php artisan key:generate --force
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec "$@"
