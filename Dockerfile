FROM php:8.3-apache
RUN a2dismod mpm_event mpm_worker && a2enmod mpm_prefork rewrite && sed -i 's!/var/www/html!/var/www/html/public!g; s/:80>/:8080>/g' /etc/apache2/sites-available/000-default.conf && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && apache2ctl configtest
COPY . /var/www/html/
EXPOSE 8080
CMD ["apache2-foreground"]
