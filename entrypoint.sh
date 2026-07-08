#!/bin/sh
set -e

echo "⏳ Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
  echo "Database not ready yet, retrying in 2s..."
  sleep 2
done
echo "✅ Database connection OK"

echo "🚀 Running migrations..."
php artisan migrate --force

# Optional but recommended for production runtime caching
# (safe now because real .env is mounted at this point)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Ready — starting php-fpm"
exec "$@"
