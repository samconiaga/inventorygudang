FROM php:8.2-fpm-alpine

# Instal dependensi Nginx, Node.js (untuk JS/jQuery build), & ekstensi MySQL
RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql intl bcmath

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Salin semua file dari GitHub ke server
COPY . .

# Build aset Frontend (JS/CSS/jQuery via Vite atau Mix)
RUN npm install && npm run build

# Build dependensi Backend (PHP/Laravel)
RUN composer install --no-dev --optimize-autoloader

# Beri hak akses ke folder Laravel agar tidak error Permission Denied
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Salin konfigurasi Nginx yang kita buat tadi
COPY nginx.conf /etc/nginx/http.d/default.conf

EXPOSE 80

# Perintah saat server/container menyala
CMD ["sh", "-c", "php artisan migrate --force && php-fpm -D && nginx -g 'daemon off;'"]
