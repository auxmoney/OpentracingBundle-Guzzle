#!/bin/bash
shopt -s extglob

cd build/testproject/
composer reinstall guzzlehttp/guzzle:^${GUZZLE_VERSION}
composer dump-autoload
cd ../../
