#!/bin/sh

echo "[entrypoint] Starting setup..."

# Setup persistent volume jika /data tersedia
if [ -d "/data" ]; then
    echo "[entrypoint] Setting up persistent storage..."
    mkdir -p /data/storage/uploads/original
    mkdir -p /data/storage/uploads/processed

    APP_STORAGE="/var/www/storage/app/public"

    if [ -L "$APP_STORAGE" ]; then
        rm "$APP_STORAGE"
    elif [ -d "$APP_STORAGE" ]; then
        cp -rn "$APP_STORAGE/." /data/storage/ 2>/dev/null || true
        rm -rf "$APP_STORAGE"
    fi

    ln -sf /data/storage "$APP_STORAGE"
    chmod -R 777 /data/storage 2>/dev/null || true
fi

# Buat storage symlink public
echo "[entrypoint] Creating storage symlink..."
php /var/www/artisan storage:link --force 2>/dev/null || true

echo "[entrypoint] Starting PHP server on port 8000..."
exec php -S 0.0.0.0:8000 -t /var/www/public
