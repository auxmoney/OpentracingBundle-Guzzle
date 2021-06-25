#!/bin/bash
shopt -s extglob

cd build/testproject/
composer require auxmoney/opentracing-bundle-guzzle
rm -fr vendor/auxmoney/opentracing-bundle-guzzle/*
cp -r ../../!(build|vendor) vendor/auxmoney/opentracing-bundle-guzzle
composer dump-autoload
cd ../../
