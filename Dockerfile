# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring \
    && docker-php-ext-enable pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application files
COPY . .

# Set proper permissions for storage directories
RUN mkdir -p storage/cache storage/doctrine \
    && chown -R www-data:www-data storage \
    && chmod -R 755 storage

# Configure Apache

# Serve the app from /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Enable rewrite and update all Apache confs to use the new docroot
RUN a2enmod rewrite \
 && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/*.conf /etc/apache2/conf-available/*.conf \
 # ensure the <Directory> allows .htaccess in /public
 && printf "\n<Directory ${APACHE_DOCUMENT_ROOT}>\n  AllowOverride All\n  Require all granted\n</Directory>\n" \
      >> /etc/apache2/apache2.conf \
 # silence ServerName warning and bind to Render's dynamic port
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Dynamic port for PaaS (Render)
ENV PORT=10000
EXPOSE ${PORT}
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

CMD ["apache2-foreground"]

