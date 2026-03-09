FROM mediawiki:latest

# RUN apt-get update && apt-get install -y git && rm -rf /var/lib/apt/lists/*
RUN git config --global --add safe.directory '*'

COPY ./build_scripts/* /tmp/
RUN /tmp/install_composer.bash

# RUN apt update && apt upgrade -y \
# && apt install -y \
# vim \
# wget \
# mariadb-client
# RUN wget 

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. PHP'nin zip uzantısını aktif et (Hızlı indirme için kritik)
RUN docker-php-ext-install zip

COPY ./conf/LocalSettings.php /var/www/html/LocalSettings.php
COPY ./conf/composer.json /var/www/html/composer.local.json

ENV COMPOSER_ALLOW_SUPERUSER=1

# RUN composer install --no-dev
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-security-blocking --no-interaction -o

COPY ./scripts/* /tmp/

# CMD php maintenance/run.php update.php --quick

# CMD ln -s /var/www/ /mnt/wiki.murmur.land
# CMD ["apache2-foreground"]
