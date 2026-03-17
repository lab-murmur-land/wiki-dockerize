FROM mediawiki:latest

RUN git config --global --add safe.directory '*'

COPY ./build_scripts/* /tmp/
RUN /tmp/install_composer.bash

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*
# PHP'nin zip uzantısını aktif et (Hızlı indirme için kritik?)
RUN docker-php-ext-install zip

RUN chown -R www-data:www-data /var/www/html/images

COPY ./conf/composer.json /var/www/html/composer.local.json

COPY ./scripts/* /tmp/
COPY Auth42/ /var/www/html/extensions/Auth42/
COPY ./conf/LocalSettings.php /var/www/html/LocalSettings.php
COPY ./conf/custom /var/www/html/custom_settings/


# COPY ./conf/.htaccess /var/www/html/.htaccess
# RUN a2enmod rewrite

# RUN sed -i "s/wfCgiToArray( \$parsed\['query'\] )/wfCgiToArray( \$parsed['query'] ?? '' )/" \
#     /var/www/html/extensions/PluggableAuth/includes/PluggableAuthHooks.php

# CMD php maintenance/run.php update.php --quick

# CMD ln -s /var/www/ /mnt/wiki.murmur.land
# CMD ["apache2-foreground"]
