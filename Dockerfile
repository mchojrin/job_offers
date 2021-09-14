FROM php:8-cli

RUN apt-get -y update
RUN apt-get -y install git zip

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer