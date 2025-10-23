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
RUN a2enmod rewrite
COPY <<EOF /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html/public
    
    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Create .htaccess file for routing
RUN echo "RewriteEngine On" > public/.htaccess \
    && echo "RewriteCond %{REQUEST_FILENAME} !-f" >> public/.htaccess \
    && echo "RewriteCond %{REQUEST_FILENAME} !-d" >> public/.htaccess \
    && echo "RewriteRule ^(.*)$ index.php [QSA,L]" >> public/.htaccess

# Expose port and set environment variable
ENV PORT=10000
EXPOSE ${PORT}

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

# Start Apache
CMD ["apache2-foreground"]
