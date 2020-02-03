#!/bin/bash

cd build/testproject/
composer auxmoney/opentracing-bundle-guzzle:dev-${BRANCH}
cd ../../
