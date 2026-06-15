FROM php:8.2-apache

# ----------------------------
# Enable required Apache modules
# ----------------------------
RUN a2enmod rewrite remoteip headers

# ----------------------------
# Make Apache respect proxy HTTPS headers
# ----------------------------
RUN echo "RemoteIPHeader X-Forwarded-For" >> /etc/apache2/apache2.conf && \
    echo "SetEnvIf X-Forwarded-Proto https HTTPS=on" >> /etc/apache2/apache2.conf

# ----------------------------
# Install system dependencies
# ----------------------------
RUN apt-get update && apt-get install -y \
    git unzip zip curl libzip-dev libonig-dev libpng-dev \
    libxml2-dev libicu-dev libjpeg-dev libfreetype6-dev build-essential \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        exif \
    && rm -rf /var/lib/apt/lists/*

# ----------------------------
# Install Composer
# ----------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ----------------------------
# Set working directory
# ----------------------------
WORKDIR /var/www/html

# ----------------------------
# Copy project files
# ----------------------------
COPY . .

# ----------------------------
# Install Laravel dependencies
# ----------------------------
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# ----------------------------
# Set Apache document root to /public
# ----------------------------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# ----------------------------
# Fix Laravel permissions
# ----------------------------
RUN chown -R www-data:www-data storage bootstrap/cache public/uploads || true && \
    chmod -R 775 storage bootstrap/cache public/uploads || true

# ----------------------------
# Expose port
# ----------------------------
EXPOSE 80

CMD ["apache2-foreground"]
