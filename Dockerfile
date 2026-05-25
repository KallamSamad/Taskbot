FROM php:8.2-apache

RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-enabled/000-default.conf

COPY . /var/www/html/

# IMPORTANT: ensure DB is included explicitly
COPY *.db /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080