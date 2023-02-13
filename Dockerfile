FROM wordpress:php8.1-apache

RUN apt update
RUN apt install zip unzip

RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer


WORKDIR /var/www/html/wp-content/plugins/linkly-wp-plugin

COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install

COPY . .

WORKDIR /var/www/html

RUN /etc/init.d/apache2 restart
