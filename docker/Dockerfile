FROM php:8.1-cli

RUN apt-get -y update
RUN apt-get -y install git zip
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD ["php", "run.php"]