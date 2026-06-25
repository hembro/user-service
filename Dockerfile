# Use the official PHP 8.2 FPM Alpine image for a lightweight footprint
FROM php:8.2-fpm-alpine

# Install system dependencies needed for Laravel, Passport, and Spatie
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    bash

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copy Composer from the official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the application code into the container
COPY . .

# Install PHP dependencies (optimized for production)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set up the storage symbolic link (Build-time)
RUN php artisan storage:link

# Fix directory permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy the entrypoint script and make it executable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set the entrypoint to our custom script
ENTRYPOINT ["docker-entrypoint.sh"]

# Start the PHP-FPM server
CMD ["php-fpm"]
