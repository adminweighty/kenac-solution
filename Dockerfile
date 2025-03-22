# Stage 1: Build Vue.js (Frontend)
FROM node:18 AS frontend
# Upgraded to Node 18 for better compatibility

# Set working directory to Laravel project root
WORKDIR /var/www/html

# Copy package files first to leverage Docker caching
COPY package.json package-lock.json webpack.mix.js ./

# Install Node dependencies
RUN npm install

# Copy actual frontend source code
COPY resources/js/ resources/js/
COPY resources/sass/ resources/sass/

# Build frontend assets
RUN npm run production

# Stage 2: Backend (Laravel)
FROM php:8.2-fpm AS backend
# Upgraded to PHP 8.2 to match Symfony 7.2

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git unzip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel application files
COPY . .

# Fix Git "dubious ownership" issue
RUN git config --global --add safe.directory /var/www/html

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Stage 3: Final Image - Serve Laravel backend and built Vue.js frontend
FROM php:8.2-fpm

# Install necessary PHP extensions
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip git unzip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy backend & built frontend assets from previous stages
COPY --from=backend /var/www/html /var/www/html
COPY --from=frontend /var/www/html/public /var/www/html/public

# Set up Laravel entry point
RUN chown -R www-data:www-data /var/www/html

# Expose port 8080 for Laravel's built-in server (configured to listen on 0.0.0.0)
EXPOSE 8080

# Start Laravel development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
