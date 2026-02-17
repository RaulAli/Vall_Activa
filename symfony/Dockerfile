FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    nginx \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP-FPM
RUN echo "pm.max_children = 20" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.start_servers = 4" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.min_spare_servers = 2" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_spare_servers = 6" >> /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
COPY <<EOF /etc/nginx/sites-available/default
server {
    listen 8000;
    server_name _;
    root /app/public;
    index index.php;

    location / {
        try_files \$uri /index.php\$is_args\$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }
}
EOF

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock symfony.lock ./

# Install dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-dev

# Copy application code
COPY . .

# Generate autoloader and warmup cache
RUN composer dump-autoload --optimize --classmap-authoritative && \
    APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear --no-warmup && \
    APP_ENV=prod APP_DEBUG=0 php bin/console cache:warmup && \
    chown -R www-data:www-data /app/var

EXPOSE 8000

# Start PHP-FPM and Nginx
CMD php-fpm -D && nginx -g 'daemon off;'
