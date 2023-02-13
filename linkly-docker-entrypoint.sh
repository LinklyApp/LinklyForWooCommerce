#!/bin/bash

cd /var/www/html/wp-content/plugins/linkly-wp-plugin || exit
composer install --no-dev --optimize-autoloader --no-interaction

cd /var/www/html || exit

chown -R www-data:www-data /var/www/html/wp-content/plugins/linkly-wp-plugin

/usr/local/bin/docker-entrypoint.sh $1



