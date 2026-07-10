FROM php:8.2-apache

# Install PostgreSQL client libraries and build dependencies, then install extensions
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Enable Apache rewrite module (useful for routing)
RUN a2enmod rewrite
