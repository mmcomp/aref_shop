FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client sendmail libpng-dev libzip-dev zip \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli pdo pdo_mysql gd zip
# zip xml iconv simplexml xmlreader

COPY ../ /workspace/
