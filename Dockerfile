# Dockerfile

FROM php:8.2-fpm-alpine AS base_php
# Consider using the exact PHP version you have locally (e.g., php:8.1-fpm-alpine if you used 8.1)

# Set maintainer label (optional)
LABEL maintainer="Azwad Fawad Hasan azwadfawadhasan@gmail.com"

# Set working directory
WORKDIR /var/www/html

# Install system dependencies required by Laravel and common PHP extensions
# Using apk (Alpine Package Keeper)
# Install system dependencies required by Laravel and common PHP extensions
# Using apk (Alpine Package Keeper)
RUN apk update && apk add --no-cache \
    build-base \
    curl \
    zip \
    unzip \
    git \
    supervisor \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl \
    sockets


# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add a non-root user for security (optional but good practice)
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www -h /home/www

# Copy existing application directory contents
COPY . /var/www/html

# Copy PHP configuration (optional, if you have custom settings)
# COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Set permissions for Laravel's writable directories
# Ensure these directories exist before trying to chown/chmod if copying an empty project
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R ug+rwx /var/www/html/storage /var/www/html/bootstrap/cache

# Change ownership of all files to the new user (if you created one)
RUN chown -R www:www /var/www/html

# Switch to the non-root user (if you created one)
# USER www # Comment out if you face permission issues initially during setup

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# The main command to run when the container starts
CMD ["php-fpm"]

# --- Development Stage (can be the same as base for simplicity now) ---
# FROM base_php AS development
# If you need Xdebug or other dev-specific tools, add them here.

# --- Production Stage (example, can be more optimized) ---
# FROM base_php AS production
# USER root # Switch back to root to install dependencies
# COPY --chown=www:www . /var/www/html
# RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress && \
#     php artisan optimize:clear && \
#     php artisan config:cache && \
#     php artisan route:cache && \
#     php artisan view:cache
# USER www
# CMD ["php-fpm"]
