#!/bin/bash

BRANCH=${TRAVIS_PULL_REQUEST_BRANCH:-$TRAVIS_BRANCH}
if [[ -z ${BRANCH} ]]
then
    echo "\$BRANCH is empty, please set it to the current development branch you want to test";
    exit 1;
fi
if [[ -z ${SYMFONY_VERSION} ]]
then
    echo "\$SYMFONY_VERSION is empty, please set it to the target symfony version you want to test against";
    exit 2;
fi

docker run -d --name jaeger \
  -e COLLECTOR_ZIPKIN_HTTP_PORT=9411 \
  -p 5775:5775/udp \
  -p 6831:6831/udp \
  -p 6832:6832/udp \
  -p 5778:5778 \
  -p 16686:16686 \
  -p 14268:14268 \
  -p 14250:14250 \
  -p 9411:9411 \
  jaegertracing/all-in-one:1.16
docker stop jaeger
mkdir -p build/
ORIGIN_DIR=`pwd`
cd build/
symfony new --version=${SYMFONY_VERSION} --no-git testproject
cd testproject/
composer config minimum-stability dev # TODO: remove as soon as all dependencies  (opentracing, jaeger-php) are released as stable version
composer config prefer-stable true    # TODO: remove as soon as all dependencies  (opentracing, jaeger-php) are released as stable version
# FIXME: cut here, separate and extract scripts to require custom packages per project
composer require auxmoney/opentracing-bundle-jaeger auxmoney/opentracing-bundle-guzzle:dev-${BRANCH}
yes | cp -rf ${ORIGIN_DIR}/Tests/Functional/TestProjectFiles/* .
composer dump-autoload
symfony console cache:clear
symfony local:server:start -d --no-tls
cd ${ORIGIN_DIR}
