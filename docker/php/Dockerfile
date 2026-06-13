FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www/star/api

# Install composer
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    nano && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install intl pdo_mysql zip bcmath gd

COPY docker/php/php.ini /usr/local/etc/php/

#  Pre-create the system directories Nette needs to write to
#    and grant ownership to the PHP web user (www-data)
RUN mkdir -p log temp && \
    chown -R www-data:www-data /var/www/star

# Force PHP-FPM to run commands and processes as www-data
#    instead of root, solving permission blocks on mounted drives
USER www-data
