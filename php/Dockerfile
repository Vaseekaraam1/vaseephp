# Use an official PHP runtime as a parent image
FROM php:8.0-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set COMPOSER_ALLOW_SUPERUSER to allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy composer.json and composer.lock
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install

# Copy application files
COPY src/ /var/www/html/

# Ensure Apache can read the document root
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]

