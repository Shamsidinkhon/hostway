#!/bin/sh

cd /var/www
mkdir .phalcon
mkdir rest/app/runtime
mkdir rest/app/runtime/logs
composer install
php vendor/bin/phalcon migration run
php-fpm
