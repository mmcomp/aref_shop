# syntax=nexus.aref-group.ir/docker/dockerfile:1
# ======================
# 1. Node build stage
# ======================
FROM nexus.aref-group.ir/docker/node:20 AS node
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ======================
# 2. PHP stage (Laravel)
# ======================
FROM nexus.aref-group.ir/docker/php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libxml2-dev \
    libpq-dev \
    libcurl4-openssl-dev \
    pkg-config \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    intl \
    gd \
    opcache \
    ftp \
    dom \
    xml \
    curl \
    fileinfo \
    soap

# Composer
COPY --from=nexus.aref-group.ir/docker/composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# -------------------------------------------------------
# Step 1: Install composer deps WITHOUT scripts
#         (vendor/ layer cached separately from app code)
# -------------------------------------------------------
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --optimize-autoloader

# -------------------------------------------------------
# Step 2: Copy full application
# -------------------------------------------------------
COPY . /var/www

# -------------------------------------------------------
# Step 3: Copy Vite build from node stage
# -------------------------------------------------------
COPY --from=node /app/public/build /var/www/public/build

# -------------------------------------------------------
# Step 4: Run composer post-install scripts
#         (package:discover needs artisan — now available)
#         SQLite :memory: bypass — no real DB at build time
# -------------------------------------------------------
RUN APP_ENV=production \
    APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    composer run-script post-autoload-dump --no-interaction

# -------------------------------------------------------
# Step 5: Create full Laravel storage directory structure
# -------------------------------------------------------
RUN mkdir -p \
    storage/app/public \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache

# -------------------------------------------------------
# Step 6: storage:link
#         SQLite bypass — real .env is mounted at runtime
# -------------------------------------------------------
RUN APP_ENV=production \
    APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    php artisan storage:link \
    && test -L public/storage \
        && echo "✅ storage:link OK → $(readlink public/storage)" \
        || { echo "❌ storage:link FAILED"; exit 1; }

# -------------------------------------------------------
# Step 7: Permissions
#         755 for all files
#         775 for writable dirs (storage + bootstrap/cache)
#         www-data owns everything
# -------------------------------------------------------
RUN chown -R www-data:www-data /var/www \
    && find /var/www -type f -exec chmod 644 {} \; \
    && find /var/www -type d -exec chmod 755 {} \; \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x artisan

# -------------------------------------------------------
# Step 8: PHP config
# -------------------------------------------------------
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY www.conf    /usr/local/etc/php-fpm.d/www.conf

# -------------------------------------------------------
# Step 9: Verify build (runs inside build — not runtime)
# -------------------------------------------------------
RUN APP_ENV=production \
    APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    php artisan about 2>&1 | grep -E "Environment|PHP|Cache|Drivers" \
    && echo "✅ Laravel boot OK"

EXPOSE 9000

# Real .env is mounted at runtime via docker-compose volume
# All DB/Redis/Mail config comes from there
CMD ["php-fpm", "-F"]
