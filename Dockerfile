# Set base image
FROM php:8.3-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    libxml2-dev \
    libssl-dev \
    libzip-dev \
    libonig-dev \
    libc-client-dev \
    libkrb5-dev \
    unzip \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Remove any existing Redis extension
RUN pecl uninstall redis || true

# Install required PHP extensions
RUN docker-php-ext-configure imap --with-imap --with-imap-ssl --with-kerberos \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath xml intl zip imap \
    && docker-php-ext-install pcntl

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Permissions for storage directory
RUN chown -R www-data:www-data /var/www/storage \
    && chmod -R 775 /var/www/storage \

# Install PHP dependencies
RUN composer install --prefer-dist --optimize-autoloader --no-dev

# Expose port
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
