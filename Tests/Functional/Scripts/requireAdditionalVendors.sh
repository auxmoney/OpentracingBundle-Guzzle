#!/bin/bash

cd build/testproject/
composer require auxmoney/opentracing-bundle-guzzle:dev-${BRANCH}
cd ../../
