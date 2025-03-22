# Stage 1: Build Vue.js (Frontend)
FROM node:16 AS frontend

# Set the working directory to the frontend directory in Laravel
WORKDIR /var/www/html

# Install dependencies for the Vue.js app (via Laravel Mix)
COPY ./resources/js/package.json ./resources/js/package-lock.json /var/www/html/resources/js/
RUN cd /var/www/html/resources/js && npm install

# Run Laravel Mix to build the Vue.js assets
RUN cd /var/www/html/resources/js && npm run prod

# Stage 2: Backend (Laravel)
FROM php:8.1-fpm AS backend

# Install necessary PHP extensions for Laravel
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory to Laravel's root and copy the Laravel files
WORKDIR /var/www/html
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Stage 3: Final Image - Serve both Laravel backend and the built Vue.js frontend
FROM php:8.1-fpm

# Install necessary PHP extensions for Laravel
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql

# Set the working directory to Laravel's root
WORKDIR /var/www/html

# Copy the Laravel app (backend) and the built frontend files from previous stages
COPY --from=backend /var/www/html /var/www/html
COPY --from=frontend /var/www/html/resources/js/dist /var/www/html/public/js

# Install Composer globally (in case the final image needs it)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set up the entry point for Laravel
CMD ["php-fpm"]

# Expose the port Heroku uses
EXPOSE 8080
