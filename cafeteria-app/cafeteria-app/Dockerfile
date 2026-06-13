FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080