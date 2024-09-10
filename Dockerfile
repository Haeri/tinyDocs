FROM php:8.2-apache
WORKDIR /var/www/html

# Copy the PHP file into the container
COPY ./pages ./pages
COPY ./public ./public
COPY ./src ./src
COPY ./.htaccess ./.htaccess
COPY ./index.php ./index.php

WORKDIR /var/www/html/src/search/
RUN ./ffsearch index -f "./docs.csv" -c "paragraph"

WORKDIR /var/www/html

# Ensure the www-data user (Apache's user) owns the web root directory
RUN chown -R www-data:www-data /var/www/html

# Enable Apache modules
RUN a2enmod rewrite

# Set the ServerName to suppress the warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Expose port 80 to the host
EXPOSE 80

# Start Apache in the foreground (as PID 1)
CMD ["apache2-foreground"]