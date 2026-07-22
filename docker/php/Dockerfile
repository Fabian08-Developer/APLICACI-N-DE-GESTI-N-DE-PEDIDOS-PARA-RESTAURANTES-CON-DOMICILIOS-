FROM php:8.4-fpm

# ===========================
# Dependencias del sistema
# ===========================
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    nginx \
    supervisor \
    nodejs \
    npm \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# ===========================
# Extensiones PHP
# ===========================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    bcmath \
    intl \
    zip \
    gd \
    opcache

# ===========================
# Composer
# ===========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ===========================
# Directorio de trabajo
# ===========================
WORKDIR /var/www

# ===========================
# Copiar proyecto
# ===========================
COPY . .

# ===========================
# Instalar dependencias Laravel
# ===========================
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

# ===========================
# Compilar Vite
# ===========================
RUN npm install

RUN npm run build

# ===========================
# Permisos
# ===========================
RUN chown -R www-data:www-data /var/www

RUN chmod -R 775 storage bootstrap/cache

# ===========================
# Supervisor
# ===========================
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# ===========================
# Puerto PHP
# ===========================
EXPOSE 9000

CMD ["/usr/bin/supervisord","-c","/etc/supervisor/conf.d/supervisord.conf"]