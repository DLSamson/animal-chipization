/user/bin/composer install
make database && make fill_tables
php-fpm --nodaemonize