#!/usr/bin/env bash

docker run \
  --rm \
  -v $(pwd):/app \
  -it job_offers $@
