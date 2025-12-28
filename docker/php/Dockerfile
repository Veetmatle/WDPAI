FROM php:8.2-fpm

# Install PostgreSQL extension and other dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd

# Set working directory
WORKDIR /app

# Copy application code
COPY . /app

# Create uploads directory and set permissions
RUN mkdir -p /app/public/uploads && chmod -R 777 /app/public/uploads

# PHP configuration
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini \
    && echo "session.cookie_httponly = 1" >> /usr/local/etc/php/php.ini \
    && echo "session.cookie_samesite = Lax" >> /usr/local/etc/php/php.ini

EXPOSE 9000

CMD ["php-fpm"]