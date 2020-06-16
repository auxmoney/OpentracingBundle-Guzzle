#!/bin/bash

cd build/testproject/
composer config repositories.origin vcs https://github.com/${PR_ORIGIN}
composer config use-github-api false
composer require auxmoney/opentracing-bundle-guzzle:dev-${BRANCH}
cd ../../
