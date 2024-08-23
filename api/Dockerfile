# Use the official PHP image with Apache
FROM php:8.1-apache

# Copy your application code to the Docker container
COPY . /var/www/html/

# Install PDO MySQL extension for database connection
RUN docker-php-ext-install pdo pdo_mysql

# Expose port 4000 to the host
EXPOSE 4000

# Change the Apache configuration to listen on port 4000
RUN sed -i 's/Listen 80/Listen 4000/' /etc/apache2/ports.conf
RUN sed -i 's/:80/:4000/' /etc/apache2/sites-enabled/000-default.conf

# Start Apache server in the foreground
CMD ["apache2-foreground"]