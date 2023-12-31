FROM registry.ulbricht.casa/jinya-cms/jinya-cms-php-base-test-image:latest

RUN install-php-extensions xdebug pdo_pgsql
