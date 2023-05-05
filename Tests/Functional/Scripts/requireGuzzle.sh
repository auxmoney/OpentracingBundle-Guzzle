#!/bin/bash
shopt -s extglob

composer reinstall guzzlehttp/guzzle:^${GUZZLE_VERSION}
composer dump-autoload
