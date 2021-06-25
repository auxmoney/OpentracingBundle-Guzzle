#!/bin/bash
shopt -s extglob

cd build/testproject/
composer require auxmoney/opentracing-bundle-guzzle:dev-drop-php-71-72 # revert this
rm -fr vendor/auxmoney/opentracing-bundle-guzzle/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-guzzle
composer dump-autoload
cd ../../
