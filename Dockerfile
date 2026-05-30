FROM php:8.4-fpm

# Install system dependencies + Node.js 20
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libpq-dev libzip-dev libicu-dev ca-certificates gnupg \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev --ignore-platform-reqs
RUN npm install && npm run build

# Memastikan folder internal Laravel yang sering ter-gitignore tetap ada di server production
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache

# Tambahkan baris ini di dalam Dockerfile untuk menaikkan limit upload PHP
RUN echo "upload_max_filesize = 20M\npost_max_size = 20M" > /usr/local/etc/php/conf.d/uploads.ini

# BUAT SYMLINK STORAGE AGAR GAMBAR BISA DIAKSES BROWSER
RUN php artisan storage:link

# Mengatur hak milik dan izin akses (read/write) secara penuh untuk folder storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

