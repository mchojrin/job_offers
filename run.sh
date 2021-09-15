#!/usr/bin/env bash

docker run \
  --network="host" \
  -v $(pwd):/app \
  -v $(pwd)/php/conf.d/xdebug.ini:/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
  -v $(pwd)/php/conf.d/error_reporting.ini:/usr/local/etc/php/conf.d/error_reporting.ini \
  -it job_offers $@