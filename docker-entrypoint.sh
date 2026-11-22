#!/bin/sh
set -e

# Cache configuration & routes for production performance
php artisan config:clear || true
php artisan view:clear || true

echo "Starting FrankenPHP server on port ${PORT:-80}..."
exec frankenphp php-server --listen ":${PORT:-80}" --root /app/public
