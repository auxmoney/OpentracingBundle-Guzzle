#!/bin/bash
shopt -s extglob

composer require guzzlehttp/guzzle:^${GUZZLE_VERSION}
composer dump-autoload
