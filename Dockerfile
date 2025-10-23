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
    gettext-base \
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

# Copy and make startup script executable
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set proper permissions for storage directories
RUN mkdir -p storage/cache storage/doctrine storage/doctrine/proxies \
    && chown -R www-data:www-data storage \
    && chmod -R 777 storage

# Configure Apache
RUN a2enmod rewrite

# Create a proper virtual host configuration
RUN echo '<VirtualHost *:${PORT}>' > /etc/apache2/sites-available/000-default.conf \
    && echo '    ServerAdmin webmaster@localhost' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    <Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-default.conf \
    && echo '        AllowOverride All' >> /etc/apache2/sites-available/000-default.conf \
    && echo '        Require all granted' >> /etc/apache2/sites-available/000-default.conf \
    && echo '        Options -Indexes' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    </Directory>' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    ErrorLog ${APACHE_LOG_DIR}/error.log' >> /etc/apache2/sites-available/000-default.conf \
    && echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined' >> /etc/apache2/sites-available/000-default.conf \
    && echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf

# Update ports.conf to use dynamic port
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Dynamic port for PaaS (Render)
ENV PORT=10000
EXPOSE ${PORT}

# Update ports.conf to listen on the dynamic port
RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf

# Ensure .htaccess exists (if not already present)
RUN [ ! -f public/.htaccess ] && echo "RewriteEngine On" > public/.htaccess \
    && echo "RewriteCond %{REQUEST_FILENAME} !-f" >> public/.htaccess \
    && echo "RewriteCond %{REQUEST_FILENAME} !-d" >> public/.htaccess \
    && echo "RewriteRule ^(.*)$ index.php [QSA,L]" >> public/.htaccess || true

# Start Apache with environment variable substitution
CMD ["/usr/local/bin/start.sh"]
