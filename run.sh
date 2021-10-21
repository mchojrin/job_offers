#!/usr/bin/env bash

docker run \
  -v $(pwd):/app \
  -it job_offers $@