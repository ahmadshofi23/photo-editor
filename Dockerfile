FROM php:8.4-fpm

# Install system dependencies + Node.js 20 + Python3 for rembg
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev \
    libxml2-dev libpq-dev libzip-dev libicu-dev ca-certificates gnupg \
    python3 python3-pip python3-venv \
    libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
    imagemagick \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install rembg dalam virtual environment agar tidak konflik dengan system packages
ENV REMBG_VENV=/opt/rembg-venv
ENV U2NET_HOME=/opt/rembg-models
RUN python3 -m venv $REMBG_VENV \
    && $REMBG_VENV/bin/pip install --upgrade pip \
    && $REMBG_VENV/bin/pip install "rembg[cpu,cli]"

# Buat symlink agar bisa dipanggil langsung sebagai 'rembg'
RUN ln -sf $REMBG_VENV/bin/rembg /usr/local/bin/rembg

# Pre-download model u2net ke lokasi global
RUN mkdir -p /opt/rembg-models \
    && $REMBG_VENV/bin/python -c "from rembg import new_session; new_session('u2net')" \
    && chmod -R 755 /opt/rembg-models

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

# Mengatur hak milik dan izin akses (read/write) secara penuh untuk folder storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public

EXPOSE 8000

# Jalankan startup script: symlink persistent volume lalu start server
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["/usr/local/bin/docker-entrypoint.sh"]

