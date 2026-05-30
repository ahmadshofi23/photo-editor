#!/bin/bash
set -e

# Railway persistent volume di-mount ke /data
# Pindahkan storage Laravel ke sana agar tidak hilang saat redeploy
PERSISTENT_STORAGE="/data/storage"
APP_STORAGE="/var/www/storage/app/public"

if [ -d "/data" ]; then
    # Buat direktori di persistent volume jika belum ada
    mkdir -p "$PERSISTENT_STORAGE/uploads/original"
    mkdir -p "$PERSISTENT_STORAGE/uploads/processed"

    # Hapus symlink lama jika ada, lalu buat symlink ke volume
    if [ -L "$APP_STORAGE" ]; then
        rm "$APP_STORAGE"
    elif [ -d "$APP_STORAGE" ]; then
        # Copy file existing ke volume sebelum symlink (migrasi pertama kali)
        cp -rn "$APP_STORAGE/." "$PERSISTENT_STORAGE/" 2>/dev/null || true
        rm -rf "$APP_STORAGE"
    fi

    ln -sf "$PERSISTENT_STORAGE" "$APP_STORAGE"
    chown -R www-data:www-data /data/storage 2>/dev/null || true
    chmod -R 775 /data/storage 2>/dev/null || true
fi

# Buat storage symlink public jika belum ada
if [ ! -L "/var/www/public/storage" ]; then
    php /var/www/artisan storage:link
fi

# Jalankan PHP server
exec php -S 0.0.0.0:8000 -t /var/www/public
