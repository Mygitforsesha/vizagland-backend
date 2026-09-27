FROM php:8.3-fpm

ARG WWWGROUP=1000
ARG WWWUSER=1000

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

WORKDIR /var/www/html

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libwebp-dev \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# User
RUN groupadd --force -g ${WWWGROUP} sail \
    && useradd -ms /bin/bash \
    --no-user-group \
    -g ${WWWGROUP} \
    -u ${WWWUSER} \
    sail

    
# Composer dependencies
COPY composer.json composer.lock* ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts

# Node dependencies
COPY package.json package-lock.json* ./

RUN npm install

# Copy Laravel application
COPY . .

# Laravel autoload
RUN composer dump-autoload --optimize

# Build Vite
RUN npm run build

# Laravel permissions
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R sail:sail storage bootstrap/cache

# PHP-FPM listen on 9000
RUN sed -i 's|^listen = .*|listen = 0.0.0.0:9000|' \
    /usr/local/etc/php-fpm.d/www.conf

# Remove default nginx config
RUN rm -f /etc/nginx/sites-enabled/default

# Create nginx config directly inside Docker image
RUN printf '%s\n' \
'server {' \
'    listen 80;' \
'    server_name _;' \
'    root /var/www/html/public;' \
'    index index.php index.html;' \
'' \
'    location / {' \
'        try_files $uri $uri/ /index.php?$query_string;' \
'    }' \
'' \
'    location ~ \.php$ {' \
'        include fastcgi_params;' \
'        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;' \
'        fastcgi_pass 127.0.0.1:9000;' \
'    }' \
'' \
'    location ~ /\.ht {' \
'        deny all;' \
'    }' \
'}' \
> /etc/nginx/conf.d/default.conf

# Create supervisor config directly inside Docker image
RUN printf '%s\n' \
'[supervisord]' \
'nodaemon=true' \
'logfile=/dev/null' \
'' \
'[program:php-fpm]' \
'command=/usr/local/sbin/php-fpm --nodaemonize' \
'autostart=true' \
'autorestart=true' \
'priority=10' \
'stdout_logfile=/dev/stdout' \
'stdout_logfile_maxbytes=0' \
'stderr_logfile=/dev/stderr' \
'stderr_logfile_maxbytes=0' \
'' \
'[program:nginx]' \
'command=/usr/sbin/nginx -g "daemon off;"' \
'autostart=true' \
'autorestart=true' \
'priority=20' \
'stdout_logfile=/dev/stdout' \
'stdout_logfile_maxbytes=0' \
'stderr_logfile=/dev/stderr' \
'stderr_logfile_maxbytes=0' \
> /etc/supervisor/conf.d/supervisord.conf

# HTTP port
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]