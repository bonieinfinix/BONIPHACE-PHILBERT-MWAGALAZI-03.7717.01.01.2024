FROM php:8.1-apache

# Copy project files into Apache document root
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite || true

EXPOSE 80

CMD ["apache2-foreground"]
