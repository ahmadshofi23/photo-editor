#!/bin/bash

PERSISTENT_STORAGE="/data/storage"
APP_STORAGE="/var/www/storage/app/public"

# Setup persistent volume jika /data tersedia
if [ -d "/data" ]; then
    mkdir -p "$PERSISTENT_STORAGE/uploads/original"
    mkdir -p "$PERSISTENT_STORAGE/uploads/processed"

    if [ -L "$APP_STORAGE" ]; then
        rm "$APP_STORAGE"
    elif [ -d "$APP_STORAGE" ]; then
        cp -rn "$APP_STORAGE/." "$PERSISTENT_STORAGE/" 2>/dev/null || true
        rm -rf "$APP_STORAGE"
    fi

    ln -sf "$PERSISTENT_STORAGE" "$APP_STORAGE"
    chown -R www-data:www-data /data/storage 2>/dev/null || true
    chmod -R 775 /data/storage 2>/dev/null || true
fi

# Buat storage symlink public
if [ ! -L "/var/www/public/storage" ]; then
    php /var/www/artisan storage:link 2>/dev/null || true
fi

# Jalankan PHP server
exec php -S 0.0.0.0:8000 -t /var/www/public
