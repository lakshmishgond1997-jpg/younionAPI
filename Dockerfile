# Use PHP + Apache image
FROM php:8.2-apache

# Copy all backend files into Apache root
COPY ./API /var/www/html/API

# Enable mod_rewrite if needed
RUN a2enmod rewrite

# Expose default HTTP port
EXPOSE 10000

# Start Apache in foreground
CMD ["apache2-foreground"]
