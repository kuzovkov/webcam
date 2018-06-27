#!/bin/sh
set -xe

# Detect the host IP
export DOCKER_BRIDGE_IP=$(ip ro | grep default | cut -d' ' -f 3)
#composer install --prefer-dist --no-progress --no-suggest
exec php-fpm
