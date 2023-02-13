FROM wordpress:php8.1-apache

RUN apt update
RUN apt install zip unzip

RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html/wp-content/plugins/linkly-wp-plugin

COPY linkly-docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/linkly-docker-entrypoint.sh

COPY . .

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/linkly-docker-entrypoint.sh"]
CMD ["apache2-foreground"]