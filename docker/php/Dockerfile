# php w wersji 8.2 z FPM (FastCGI Process Manager) - do obsługi PHP w trybie FastCGI bo jest nginx
FROM php:8.2-fpm

# Biblioteki linux potrzebne, konfiguracja bibliotek php, gd do ogarniania obrazków, PDO do bazy (to ważne)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql gd 

WORKDIR /app
COPY . /app

# Folder na uploady z uprawnieniami, daje 777 
RUN mkdir -p /app/public/uploads && chmod -R 777 /app/public/uploads

# Konfuguracja PHP - zwiększone do 20MB limity uploadu i post, max czas wykonania skryptu 300s, zabezpieczenia ciasteczek sesyjnych
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/php.ini \
    && echo "session.cookie_httponly = 1" >> /usr/local/etc/php/php.ini \
    && echo "session.cookie_samesite = Lax" >> /usr/local/etc/php/php.ini

# czeka na nginx na porcie 9000, cmd odpala proces menagara phpa
EXPOSE 9000
CMD ["php-fpm"]