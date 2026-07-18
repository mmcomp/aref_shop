#!/bin/sh
set -e



echo "🚀 Running migrations..."
php artisan migrate --force

# Optional but recommended for production runtime caching
# (safe now because real .env is mounted at this point)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Ready — starting php-fpm"
exec "$@"
