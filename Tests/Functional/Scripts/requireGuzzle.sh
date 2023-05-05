#!/bin/bash
shopt -s extglob

composer reinstall guzzlehttp/guzzle:^${GUZZLE_VERSION} --with-dependencies
composer dump-autoload
