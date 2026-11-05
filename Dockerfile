# Local or server run with Docker:  docker compose up -d  →  http://localhost:8080/install/
FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev libzip-dev libsqlite3-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install -j"$(nproc)" gd pdo_mysql pdo_sqlite zip exif \
 && a2enmod rewrite headers expires deflate \
 && sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
 && printf "upload_max_filesize=32M\npost_max_size=64M\nmemory_limit=256M\nmax_execution_time=300\n" > /usr/local/etc/php/conf.d/venus.ini \
 && rm -rf /var/lib/apt/lists/*
COPY public/ /var/www/html/
RUN rm -f /var/www/html/router.php && chown -R www-data:www-data /var/www/html/storage /var/www/html/uploads
VOLUME ["/var/www/html/storage", "/var/www/html/uploads"]
