FROM php:8.1-apache

RUN a2enmod rewrite

RUN apt-get update \
  && apt-get install -y libzip-dev libpq-dev git wget --no-install-recommends \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pgsql pdo_pgsql zip;

RUN wget https://getcomposer.org/download/2.0.9/composer.phar \
    && mv composer.phar /usr/bin/composer && chmod +x /usr/bin/composer

COPY ./docker/php/apache.conf /etc/apache2/sites-enabled/000-default.conf
#COPY ./docker/entrypoint.sh /entrypoint.sh
#COPY . /var/www

WORKDIR /var/www

#RUN chmod +x /entrypoint.sh

CMD ["apache2-foreground"]

#ENTRYPOINT ["/entrypoint.sh"]