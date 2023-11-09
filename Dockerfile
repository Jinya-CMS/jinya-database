FROM harbor.ulbricht.casa/jinya/jinya-cms-php-base-test-image:latest

RUN install-php-extensions xdebug pdo_pgsql
