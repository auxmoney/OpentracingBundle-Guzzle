#!/bin/bash
shopt -s extglob

cd build/testproject/
composer require guzzlehttp/guzzle:^${GUZZLE_VERSION} --with-dependencies
composer dump-autoload
cd ../../
