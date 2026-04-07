# Stage 1: Install dependencies with Composer
FROM composer:2 as vendor

WORKDIR /app

COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist


# Stage 2: Setup the application
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    g++ \
    make \
    autoconf \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    jpeg-dev \
    freetype-dev \
    oniguruma-dev \
    libjpeg-turbo-dev \
    nginx

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml \
    zip

# Copy application code
COPY . .

# Copy vendor files from the first stage
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
