FROM php:8.2-apache

# PDO drivers for SQLite (default) + MySQL (optional)
RUN apt-get update && apt-get install -y libsqlite3-dev \
 && rm -rf /var/lib/apt/lists/* \
 && docker-php-ext-install pdo pdo_sqlite pdo_mysql

COPY . /var/www/html/

# Writable dirs for the SQLite DB + uploaded photos
RUN mkdir -p /var/www/html/data /var/www/html/uploads \
 && chown -R www-data:www-data /var/www/html/data /var/www/html/uploads \
 && chmod -R 775 /var/www/html/data /var/www/html/uploads

# Render gives the port via $PORT — point Apache at it, then serve
CMD sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf \
 && docker-php-entrypoint apache2-foreground
